from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
import fitz
import os
import faiss
from sentence_transformers import SentenceTransformer
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer
from reportlab.lib.styles import getSampleStyleSheet

app = Flask(__name__)
CORS(app)

# ===============================
# MODEL FOR SEARCH
# ===============================

embedder = SentenceTransformer("all-MiniLM-L6-v2")

pdf_chunks = []
index = None
qa_history = []

UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# ===============================
# EXTRACT TEXT FROM PDF
# ===============================

def extract_text_from_pdf(path):

    doc = fitz.open(path)
    text = ""

    for page in doc:
        text += page.get_text()

    return text


# ===============================
# SPLIT TEXT INTO CHUNKS
# ===============================

def split_text(text, chunk_size=200):

    words = text.split()
    chunks = []

    for i in range(0, len(words), chunk_size):
        chunk = " ".join(words[i:i + chunk_size])
        chunks.append(chunk)

    return chunks


# ===============================
# CREATE VECTOR DATABASE
# ===============================

def create_vector_store(chunks):

    global index

    embeddings = embedder.encode(chunks)

    dim = embeddings.shape[1]

    index = faiss.IndexFlatL2(dim)
    index.add(embeddings)

    return index


# ===============================
# SEARCH ANSWER IN PDF
# ===============================

def search_pdf(question, top_k=3):

    if index is None:
        return "Please upload a PDF first."

    q_embedding = embedder.encode([question])

    distances, ids = index.search(q_embedding, top_k)

    results = []

    for i in ids[0]:
        if i < len(pdf_chunks):
            results.append(pdf_chunks[i])

    return "\n\n".join(results)


# ===============================
# GENERATE QUESTIONS FROM PDF
# ===============================

def generate_questions(num_questions=5):

    questions = []

    if len(pdf_chunks) == 0:
        return []

    for i in range(min(num_questions, len(pdf_chunks))):

        chunk = pdf_chunks[i]

        question = f"What is explained in the following text?"

        answer = chunk

        questions.append((question, answer))

    return questions


# ===============================
# UPLOAD PDF API
# ===============================

@app.route("/upload", methods=["POST"])
def upload_pdf():

    global pdf_chunks

    if "pdf" not in request.files:
        return jsonify({"error": "No PDF uploaded"})

    file = request.files["pdf"]

    path = os.path.join(UPLOAD_FOLDER, file.filename)
    file.save(path)

    text = extract_text_from_pdf(path)

    pdf_chunks = split_text(text)

    create_vector_store(pdf_chunks)

    return jsonify({"status": "PDF uploaded and processed successfully"})


# ===============================
# ASK QUESTION API
# ===============================

@app.route("/ask", methods=["POST"])
def ask_question():

    data = request.json
    question = data.get("question")

    answer = search_pdf(question)

    qa_history.append((question, answer))

    return jsonify({"answer": answer})


# ===============================
# GENERATE QUESTIONS API
# ===============================

@app.route("/generate_questions", methods=["GET"])
def generate_questions_api():

    global qa_history

    generated = generate_questions(5)

    for q, a in generated:
        qa_history.append((q, a))

    return jsonify({
        "questions_answers": [
            {"question": q, "answer": a} for q, a in generated
        ]
    })


# ===============================
# DOWNLOAD Q&A PDF
# ===============================

@app.route("/generate_pdf")
def generate_pdf():

    path = "qa_output.pdf"

    styles = getSampleStyleSheet()
    story = []

    for q, a in qa_history:

        story.append(Paragraph(f"<b>Q:</b> {q}", styles["Normal"]))
        story.append(Paragraph(f"<b>A:</b> {a}", styles["Normal"]))
        story.append(Spacer(1, 12))

    doc = SimpleDocTemplate(path)
    doc.build(story)

    return send_file(path, as_attachment=True)


# ===============================
# RUN SERVER
# ===============================

if __name__ == "__main__":
    app.run(debug=True)