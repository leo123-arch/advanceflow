<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Resume Generator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f5f7fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            min-height: 100vh;
        }
        .user-info {
            background-color: #e8f4fc;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
            max-width: 800px;
            border-left: 5px solid #2c5aa0;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 20px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 3px solid #2c5aa0;
        }
        h1 {
            color: #2c5aa0;
            font-size: 2.8rem;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
            font-size: 1.1rem;
        }
        .section {
            margin-bottom: 35px;
        }
        .section-title {
            color: #2c5aa0;
            font-size: 1.5rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eaeaea;
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: #2c5aa0;
        }
        .subsection {
            background-color: #f9fbfd;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #2c5aa0;
            margin-top: 15px;
        }
        .subsection h3 {
            color: #2c5aa0;
            margin-bottom: 12px;
            font-size: 1.3rem;
        }
        ul {
            padding-left: 20px;
            line-height: 1.7;
        }
        li {
            margin-bottom: 10px;
            color: #444;
        }
        p {
            line-height: 1.7;
            color: #444;
            margin-bottom: 15px;
        }
        .button-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 30px auto;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 35px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            font-weight: 600;
        }
        .download-btn {
            background-color: #2c5aa0;
            color: white;
            box-shadow: 0 4px 10px rgba(44, 90, 160, 0.3);
        }
        .download-btn:hover {
            background-color: #1e3d6f;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(44, 90, 160, 0.4);
            color: white;
        }
        .php-btn {
            background-color: #4CAF50;
            color: white;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }
        .php-btn:hover {
            background-color: #3d8b40;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
            color: white;
        }
        .btn:active {
            transform: translateY(0);
        }
        .btn svg {
            width: 20px;
            height: 20px;
        }
        .footer-note {
            text-align: center;
            color: #777;
            font-size: 0.9rem;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .loading {
            color: #666;
            font-style: italic;
        }
        @media print {
            .button-container, .user-info {
                display: none;
            }
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- User Info Display -->
    <div id="userInfoSection" class="user-info">
        <p>Generating resume for: <strong id="loggedInUser" class="loading">Loading faculty data...</strong></p>
        <p id="userEmailDisplay" style="font-size: 0.9rem; color: #666; margin-top: 5px;" class="loading">Email: loading...</p>
    </div>

    <!-- Resume Content -->
    <h1>PROFESSIONAL RESUME</h1>
    
    <div class="container" id="resume-content">
        <div class="header">
            <h2 id="userNameDisplay" class="loading">Loading Faculty Name...</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <strong>Email:</strong> <span id="userEmail" class="loading">loading...</span>
                </div>
                <div class="contact-item">
                    <strong>Experience:</strong> <span id="userExperience" class="loading">loading...</span>
                </div>
                <div class="contact-item">
                    <strong>Department:</strong> <span id="userDepartment" class="loading">loading...</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">PROFESSIONAL SUMMARY</div>
            <div class="subsection">
                <h3>TECHNIC EXPERIENCE</h3>
                <ul>
                    <li>Extensive teaching experience with a total of <span id="teachingHours" class="loading">0</span> teaching hours recorded. Demonstrated commitment to delivering high-quality education and fostering engaging learning environments.</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">RESEARCH CONTRIBUTIONS</div>
            <p>Active researcher with <span id="researchCount" class="loading">0</span> research document(s) contributed to the academic community. Engaged in scholarly activities with focus on advancing the field of IT.</p>
        </div>
        
        <div class="section">
            <div class="section-title">QUALIFICATION</div>
            <p id="userQualification" class="loading">Loading qualification...</p>
        </div>
        
        <div class="footer-note">
            Last updated: <span id="currentDate">January 2025</span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="button-container">
        <button class="btn download-btn" onclick="downloadPDF()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download as PDF
        </button>
        
        <a href="generate_resume.php" class="btn php-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5v-9zM1.5 3a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13z"/>
                <path d="M2 4.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-1z"/>
            </svg>
            Back
        </a>
    </div>

    <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        
        // ===========================================
        // FETCH FACULTY DATA FROM PHP
        // ===========================================
        async function fetchFacultyData() {
            try {
                console.log('Fetching faculty data...');
                
                // Fetch faculty data from your PHP endpoint
                const response = await fetch('get_faculty_data.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Faculty data received:', data);
                
                return data;
                
            } catch (error) {
                console.error('Error fetching faculty data:', error);
                
                // Return fallback data
                return {
                    name: 'Faculty Member',
                    email: 'faculty@example.com',
                    department: 'Computer Science',
                    experience: '0',
                    qualification: 'PhD in Computer Science',
                    teaching_hours: 0,
                    research_count: 0
                };
            }
        }
        
        // ===========================================
        // APPLY FACULTY DATA TO PAGE
        // ===========================================
        async function applyFacultyData() {
            try {
                // Show loading state
                document.querySelectorAll('.loading').forEach(el => {
                    el.classList.remove('loading');
                });
                
                // Fetch data
                const faculty = await fetchFacultyData();
                
                // Update all displays with faculty data
                document.getElementById('loggedInUser').textContent = faculty.name;
                document.getElementById('userNameDisplay').textContent = faculty.name;
                document.getElementById('userEmail').textContent = faculty.email;
                document.getElementById('userEmailDisplay').textContent = `Email: ${faculty.email}`;
                document.getElementById('userExperience').textContent = faculty.experience + ' years';
                document.getElementById('userDepartment').textContent = faculty.department;
                document.getElementById('teachingHours').textContent = faculty.teaching_hours || 0;
                document.getElementById('researchCount').textContent = faculty.research_count || 0;
                document.getElementById('userQualification').textContent = faculty.qualification || 'Not specified';
                
                // Update current date
                const currentDate = new Date().toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                document.getElementById('currentDate').textContent = currentDate;
                
                console.log('Faculty data applied successfully');
                
            } catch (error) {
                console.error('Error applying faculty data:', error);
                alert('Failed to load faculty data. Please try again.');
            }
        }
        
        // ===========================================
        // PDF DOWNLOAD FUNCTION
        // ===========================================
        async function downloadPDF() {
            const button = document.querySelector('.download-btn');
            const originalText = button.innerHTML;
            
            try {
                // Show loading state
                button.innerHTML = 'Generating PDF...';
                button.disabled = true;
                
                // Get current faculty data
                const faculty = await fetchFacultyData();
                
                console.log('Starting PDF generation for:', faculty.name);
                
                // Capture the resume content as an image
                const element = document.getElementById('resume-content');
                
                // Wait a moment to ensure everything is rendered
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const canvas = await html2canvas(element, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: true
                });
                
                console.log('Canvas created, generating PDF...');
                
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });
                
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                
                // Calculate image dimensions to fit page
                const imgWidth = pageWidth - 20; // 10mm margins on each side
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                // Add header with faculty's name
                pdf.setFontSize(20);
                pdf.setTextColor(44, 90, 160);
                pdf.text('PROFESSIONAL RESUME', pageWidth / 2, 15, { align: 'center' });
                
                pdf.setFontSize(16);
                pdf.setTextColor(0, 0, 0);
                pdf.text(faculty.name, pageWidth / 2, 25, { align: 'center' });
                
                // Add the resume image
                pdf.addImage(imgData, 'PNG', 10, 35, imgWidth, imgHeight);
                
                // Add footer with page number
                const pageCount = pdf.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    pdf.setPage(i);
                    pdf.setFontSize(10);
                    pdf.setTextColor(100, 100, 100);
                    pdf.text(`Page ${i} of ${pageCount}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
                    pdf.text('Faculty Resume - Generated on ' + new Date().toLocaleDateString(), pageWidth / 2, pageHeight - 5, { align: 'center' });
                }
                
                // Generate filename and save
                const fileName = faculty.name.replace(/\s+/g, '_') + '_Faculty_Resume.pdf';
                console.log('Saving PDF as:', fileName);
                pdf.save(fileName);
                
                console.log('PDF download started successfully');
                
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Failed to generate PDF. Please try again or use the PHP/FPDF button.');
            } finally {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        // ===========================================
        // INITIALIZE PAGE
        // ===========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, fetching faculty data...');
            applyFacultyData();
            
            // Also allow printing with Ctrl+P
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    downloadPDF();
                }
            });
        });
    </script>
</body>
</html>