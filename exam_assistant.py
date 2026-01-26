from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
import pdfplumber
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas

app = Flask(__name__)
CORS(app)   # IMPORTANT: Fixes browser blocking

document_text = ""
qa_history = []

# ---------------- PDF UPLOAD ----------------
@app.route("/upload", methods=["POST"])
def upload_pdf():
    global document_text, qa_history
    qa_history = []

    if "pdf" not in request.files:
        return jsonify({"error": "PDF file not received"})

    file = request.files["pdf"]

    if file.filename == "":
        return jsonify({"error": "No file selected"})

    text = ""

    try:
        with pdfplumber.open(file) as pdf:
            for page in pdf.pages:
                page_text = page.extract_text()
                if page_text:
                    text += page_text + "\n"
    except Exception:
        return jsonify({"error": "Failed to read PDF"})

    if text.strip() == "":
        return jsonify({"error": "PDF has no readable text"})

    document_text = text
    return jsonify({"status": "PDF uploaded successfully"})

# ---------------- ASK QUESTION ----------------
@app.route("/ask", methods=["POST"])
def ask_question():
    global document_text, qa_history

    if document_text.strip() == "":
        return jsonify({"answer": "Please upload a PDF first."})

    data = request.get_json()
    question = data.get("question", "").lower()

    # Split into sentences
    sentences = [s.strip() for s in document_text.split(".") if len(s.strip()) > 30]

    # ---------- SUMMARIZE ----------
    if "summary" in question or "summarize" in question:
        summary = " ".join(sentences[:10])
        qa_history.append({"question": question, "answer": summary})
        return jsonify({"answer": summary})

    # ---------- GENERATE QUESTIONS ----------
    if "generate question" in question or "practice question" in question:
        questions = []
        for s in sentences[:20]:
            words = s.split()
            if len(words) > 8:
                q = "What is meant by " + " ".join(words[:6]) + "?"
                questions.append(q)

        result = "\n".join(questions[:8])
        qa_history.append({"question": question, "answer": result})
        return jsonify({"answer": result})

    # ---------- EXPLAIN CONCEPT ----------
    if "explain" in question or "concept" in question:
        vectorizer = TfidfVectorizer()
        vectors = vectorizer.fit_transform(sentences + [question])
        similarity = cosine_similarity(vectors[-1], vectors[:-1])
        top_indexes = similarity.argsort()[0][-5:]

        explanation = " ".join([sentences[i] for i in top_indexes])
        qa_history.append({"question": question, "answer": explanation})
        return jsonify({"answer": explanation})

    # ---------- NORMAL QUESTION ANSWER ----------
    vectorizer = TfidfVectorizer()
    vectors = vectorizer.fit_transform(sentences + [question])
    similarity = cosine_similarity(vectors[-1], vectors[:-1])
    best_match = similarity.argmax()

    answer = sentences[best_match]
    qa_history.append({"question": question, "answer": answer})

    return jsonify({"answer": answer})


# ---------------- GENERATE PDF ----------------
@app.route("/generate_pdf", methods=["GET"])
def generate_pdf():
    file_path = "Exam_QA_Report.pdf"
    pdf = canvas.Canvas(file_path, pagesize=A4)

    width, height = A4
    y = height - 50

    pdf.setFont("Helvetica-Bold", 16)
    pdf.drawString(50, y, "AI Exam Assistant – Question Answer Report")
    y -= 40

    pdf.setFont("Helvetica", 11)

    for i, qa in enumerate(qa_history, start=1):
        if y < 100:
            pdf.showPage()
            y = height - 50
            pdf.setFont("Helvetica", 11)

        pdf.setFont("Helvetica-Bold", 11)
        pdf.drawString(50, y, f"Q{i}: {qa['question']}")
        y -= 18

        pdf.setFont("Helvetica", 11)
        pdf.drawString(60, y, f"Answer: {qa['answer']}")
        y -= 30

    pdf.save()
    return send_file(file_path, as_attachment=True)

if __name__ == "__main__":
    app.run(debug=True)
