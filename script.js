// ============================================================================
// TAB NAVIGATION
// ============================================================================
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        const tab = button.getAttribute('data-tab');

        // Update active tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');

        // Show corresponding section
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${tab}-section`).classList.add('active');
        
        // Scroll to top of form
        document.querySelector('.form-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// Form navigation
document.querySelectorAll('[data-next]').forEach(button => {
    button.addEventListener('click', () => {
        const nextTab = button.getAttribute('data-next');
        
        // Validate current tab before proceeding
        const currentTab = button.closest('.form-section').id.replace('-section', '');
        if (!validateTab(currentTab)) {
            return;
        }

        // Update active tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-tab') === nextTab) {
                btn.classList.add('active');
            }
        });

        // Show corresponding section
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${nextTab}-section`).classList.add('active');
        
        // Scroll to top of form
        document.querySelector('.form-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

document.querySelectorAll('[data-prev]').forEach(button => {
    button.addEventListener('click', () => {
        const prevTab = button.getAttribute('data-prev');

        // Update active tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-tab') === prevTab) {
                btn.classList.add('active');
            }
        });

        // Show corresponding section
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${prevTab}-section`).classList.add('active');
        
        // Scroll to top of form
        document.querySelector('.form-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// ============================================================================
// TEMPLATE SELECTION
// ============================================================================
document.querySelectorAll('.template-option').forEach(option => {
    option.addEventListener('click', () => {
        const template = option.getAttribute('data-template');

        // Update active template
        document.querySelectorAll('.template-option').forEach(opt => {
            opt.classList.remove('active');
        });
        option.classList.add('active');

        // Show corresponding template
        document.querySelectorAll('.resume-template').forEach(tpl => {
            tpl.classList.remove('active');
        });
        document.getElementById(template).classList.add('active');

        // Update preview
        updatePreview();
        
        // Update template in form data
        const templateInput = document.getElementById('selectedTemplate');
        if (!templateInput) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.id = 'selectedTemplate';
            input.value = template;
            document.querySelector('form')?.appendChild(input);
        } else {
            templateInput.value = template;
        }
    });
});

// ============================================================================
// DYNAMIC FIELD MANAGEMENT
// ============================================================================
let educationCount = 1;
let experienceCount = 1;
let certificationCount = 1;
let projectCount = 1;

function addField(type) {
    let container, count, html;

    switch (type) {
        case 'education':
            container = document.getElementById('education-fields');
            count = ++educationCount;
            html = `
                <div class="dynamic-field" data-field-id="edu-${count}">
                    <div class="dynamic-field-header">
                        <h3>Education #${count}</h3>
                        <button type="button" class="remove-btn" onclick="removeField(this, 'education')">Remove</button>
                    </div>
                    <div class="form-group">
                        <label for="degree-${count}">Degree/Certificate *</label>
                        <input type="text" id="degree-${count}" class="education-input" placeholder="Bachelor of Science in Computer Science" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="institution-${count}">Institution *</label>
                        <input type="text" id="institution-${count}" class="education-input" placeholder="University of Technology" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="eduLocation-${count}">Location</label>
                        <input type="text" id="eduLocation-${count}" class="education-input" placeholder="City, State">
                    </div>
                    
                    <div class="form-group">
                        <label for="graduationDate-${count}">Graduation Date</label>
                        <input type="month" id="graduationDate-${count}" class="education-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="gpa-${count}">Percentage</label>
                        <input type="text" id="gpa-${count}" class="education-input" placeholder="98 %">
                    </div>
                </div>
            `;
            break;

        case 'experience':
            container = document.getElementById('experience-fields');
            count = ++experienceCount;
            html = `
                <div class="dynamic-field" data-field-id="exp-${count}">
                    <div class="dynamic-field-header">
                        <h3>Experience #${count}</h3>
                        <button type="button" class="remove-btn" onclick="removeField(this, 'experience')">Remove</button>
                    </div>
                    <div class="form-group">
                        <label for="jobTitle-${count}">Job Title *</label>
                        <input type="text" id="jobTitle-${count}" class="experience-input" placeholder="Software Developer" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company-${count}">Company *</label>
                        <input type="text" id="company-${count}" class="experience-input" placeholder="Tech Solutions Inc." required>
                    </div>
                    
                    <div class="form-group">
                        <label for="workLocation-${count}">Location</label>
                        <input type="text" id="workLocation-${count}" class="experience-input" placeholder="San Francisco, CA">
                    </div>
                    
                    <div class="form-group">
                        <label for="startDate-${count}">Start Date</label>
                        <input type="month" id="startDate-${count}" class="experience-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="endDate-${count}">End Date</label>
                        <input type="month" id="endDate-${count}" class="experience-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="jobDescription-${count}">Job Description</label>
                        <textarea id="jobDescription-${count}" class="experience-input" rows="3" placeholder="Describe your responsibilities and achievements"></textarea>
                    </div>
                </div>
            `;
            break;

        case 'certification':
            container = document.getElementById('certification-fields');
            count = ++certificationCount;
            html = `
                <div class="dynamic-field" data-field-id="cert-${count}">
                    <div class="dynamic-field-header">
                        <h4>Certification #${count}</h4>
                        <button type="button" class="remove-btn" onclick="removeField(this, 'certification')">Remove</button>
                    </div>
                    <div class="form-group">
                        <label for="certName-${count}">Certification Name</label>
                        <input type="text" id="certName-${count}" class="certification-input" placeholder="AWS Certified Developer">
                    </div>
                    
                    <div class="form-group">
                        <label for="certIssuer-${count}">Issuing Organization</label>
                        <input type="text" id="certIssuer-${count}" class="certification-input" placeholder="Amazon Web Services">
                    </div>
                    
                    <div class="form-group">
                        <label for="certDate-${count}">Date Issued</label>
                        <input type="month" id="certDate-${count}" class="certification-input">
                    </div>
                </div>
            `;
            break;

        case 'project':
            container = document.getElementById('project-fields');
            count = ++projectCount;
            html = `
                <div class="dynamic-field" data-field-id="proj-${count}">
                    <div class="dynamic-field-header">
                        <h3>Project #${count}</h3>
                        <button type="button" class="remove-btn" onclick="removeField(this, 'project')">Remove</button>
                    </div>
                    <div class="form-group">
                        <label for="projectTitle-${count}">Project Title *</label>
                        <input type="text" id="projectTitle-${count}" class="project-input" placeholder="E-commerce Website" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="projectDescription-${count}">Project Description</label>
                        <textarea id="projectDescription-${count}" class="project-input" rows="3" placeholder="Describe your project and its purpose"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="projectTechnologies-${count}">Technologies Used</label>
                        <input type="text" id="projectTechnologies-${count}" class="project-input" placeholder="React, Node.js, MongoDB">
                    </div>
                    
                    <div class="form-group">
                        <label for="projectLink-${count}">Project Link</label>
                        <input type="url" id="projectLink-${count}" class="project-input" placeholder="https://github.com/johndoe/ecommerce">
                    </div>
                    
                    <div class="form-group">
                        <label for="projectFeatures-${count}">Key Features</label>
                        <textarea id="projectFeatures-${count}" class="project-input" rows="3" placeholder="List the main features of your project"></textarea>
                    </div>
                </div>
            `;
            break;
    }

    if (container) {
        container.insertAdjacentHTML('beforeend', html);
        
        // Add event listeners to new inputs
        const newInputs = container.lastElementChild.querySelectorAll('input, textarea, select');
        newInputs.forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });
        
        updatePreview();
        
        // Scroll to the newly added field
        setTimeout(() => {
            container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
}

function removeField(button, type) {
    const field = button.closest('.dynamic-field');
    if (field) {
        field.style.transition = 'opacity 0.3s, transform 0.3s';
        field.style.opacity = '0';
        field.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            field.remove();

            // Update counts if needed
            if (type === 'education') educationCount--;
            if (type === 'experience') experienceCount--;
            if (type === 'certification') certificationCount--;
            if (type === 'project') projectCount--;
            
            updatePreview();
            showMessage('Field removed successfully', 'info');
        }, 300);
    }
}

// ============================================================================
// PREVIEW UPDATES
// ============================================================================
function formatDate(dateString) {
    if (!dateString) return '';
    try {
        // Add a day to make it a valid date
        const date = new Date(dateString + '-01');
        if (isNaN(date.getTime())) return dateString;
        
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    } catch (e) {
        return dateString;
    }
}

function updatePreview() {
    // Personal information
    const name = document.getElementById('fullName')?.value || 'Your Name';
    const email = document.getElementById('email')?.value || 'email@example.com';
    const phone = document.getElementById('phone')?.value || '(123) 456-7890';
    const address = document.getElementById('address')?.value || 'Your City, State';
    const summary = document.getElementById('summary')?.value || 'Experienced professional with skills in various technologies.';
    const linkedin = document.getElementById('linkedin')?.value || '';
    const github = document.getElementById('github')?.value || '';
    const portfolio = document.getElementById('portfolio')?.value || '';

    // Update both templates
    updateTemplate1(name, email, phone, address, summary, linkedin, github, portfolio);
    updateTemplate2(name, email, phone, address, summary, linkedin, github, portfolio);

    // Update dynamic sections for both templates
    updateDynamicSections();

    // Apply style customizations
    applyStyles();
}

function updateTemplate1(name, email, phone, address, summary, linkedin, github, portfolio) {
    const previewName = document.getElementById('preview-name');
    const previewEmail = document.getElementById('preview-email');
    const previewPhone = document.getElementById('preview-phone');
    const previewAddress = document.getElementById('preview-address');
    const previewSummary = document.getElementById('preview-summary');
    const previewSocial = document.getElementById('preview-social');
    const previewSkills = document.getElementById('preview-skills');
    
    if (previewName) previewName.textContent = name || 'Your Name';
    if (previewEmail) previewEmail.textContent = email || 'email@example.com';
    if (previewPhone) previewPhone.textContent = phone || '(123) 456-7890';
    if (previewAddress) previewAddress.textContent = address || 'Your City, State';
    if (previewSummary) previewSummary.textContent = summary || 'Experienced professional...';
    
    if (previewSocial) {
        let socialHTML = '';
        if (linkedin) socialHTML += `<span><i class="fab fa-linkedin"></i> ${linkedin}</span>`;
        if (github) socialHTML += `<span><i class="fab fa-github"></i> ${github}</span>`;
        if (portfolio) socialHTML += `<span><i class="fas fa-globe"></i> ${portfolio}</span>`;
        previewSocial.innerHTML = socialHTML || '<span>Add social links in Personal Info tab</span>';
    }

    if (previewSkills) {
        const skills = document.getElementById('skills')?.value || 'JavaScript, HTML, CSS';
        const skillTags = skills.split(',').map(skill => `<span class="skill-tag">${skill.trim()}</span>`).join('');
        previewSkills.innerHTML = skillTags || '<span class="skill-tag">Add skills in Skills tab</span>';
    }
}

function updateTemplate2(name, email, phone, address, summary, linkedin, github, portfolio) {
    const previewName = document.getElementById('preview-name-2');
    const previewEmail = document.getElementById('preview-email-2');
    const previewPhone = document.getElementById('preview-phone-2');
    const previewAddress = document.getElementById('preview-address-2');
    const previewSummary = document.getElementById('preview-summary-2');
    const previewSocial = document.getElementById('preview-social-2');
    const previewSkills = document.getElementById('preview-skills-2');
    
    if (previewName) previewName.textContent = name || 'Your Name';
    if (previewEmail) previewEmail.textContent = email || 'email@example.com';
    if (previewPhone) previewPhone.textContent = phone || '(123) 456-7890';
    if (previewAddress) previewAddress.textContent = address || 'Your City, State';
    if (previewSummary) previewSummary.textContent = summary || 'Experienced professional...';
    
    if (previewSocial) {
        let socialHTML = '';
        if (linkedin) socialHTML += `<div><i class="fab fa-linkedin"></i> ${linkedin}</div>`;
        if (github) socialHTML += `<div><i class="fab fa-github"></i> ${github}</div>`;
        if (portfolio) socialHTML += `<div><i class="fas fa-globe"></i> ${portfolio}</div>`;
        previewSocial.innerHTML = socialHTML || '<div>Add social links</div>';
    }

    if (previewSkills) {
        const skills = document.getElementById('skills')?.value || 'JavaScript, HTML, CSS';
        previewSkills.textContent = skills || 'Add skills in Skills tab';
    }
}

function updateDynamicSections() {
    updateEducationPreview();
    updateExperiencePreview();
    updateCertificationPreview();
    updateProjectPreview();
}

function updateEducationPreview() {
    const educationFields = document.querySelectorAll('#education-fields .dynamic-field');
    
    // Template 1
    const previewEducation = document.getElementById('preview-education');
    if (previewEducation) {
        let educationHTML1 = '';
        
        if (educationFields.length === 0) {
            educationHTML1 = '<div class="education-item"><em>No education added yet</em></div>';
        } else {
            educationFields.forEach((field, index) => {
                const degree = field.querySelector(`input[id^="degree"]`)?.value || `Education ${index + 1}`;
                const institution = field.querySelector(`input[id^="institution"]`)?.value || 'Institution';
                const location = field.querySelector(`input[id^="eduLocation"]`)?.value || '';
                const graduationDate = field.querySelector(`input[id^="graduationDate"]`)?.value;
                const gpa = field.querySelector(`input[id^="gpa"]`)?.value || '';
                
                educationHTML1 += `
                    <div class="education-item">
                        <div class="item-header">
                            <span>${degree}</span>
                            ${graduationDate ? `<span>${formatDate(graduationDate)}</span>` : ''}
                        </div>
                        <div>${institution}${location ? `, ${location}` : ''}</div>
                        ${gpa ? `<p>Percentage: ${gpa}</p>` : ''}
                    </div>
                `;
            });
        }
        previewEducation.innerHTML = educationHTML1;
    }
    
    // Template 2
    const previewEducation2 = document.getElementById('preview-education-2');
    if (previewEducation2) {
        let educationHTML2 = '';
        
        if (educationFields.length === 0) {
            educationHTML2 = '<div class="education-item"><em>No education added yet</em></div>';
        } else {
            educationFields.forEach((field, index) => {
                const degree = field.querySelector(`input[id^="degree"]`)?.value || `Education ${index + 1}`;
                const institution = field.querySelector(`input[id^="institution"]`)?.value || 'Institution';
                const location = field.querySelector(`input[id^="eduLocation"]`)?.value || '';
                const graduationDate = field.querySelector(`input[id^="graduationDate"]`)?.value;
                const gpa = field.querySelector(`input[id^="gpa"]`)?.value || '';
                
                educationHTML2 += `
                    <div class="education-item">
                        <div class="item-header">
                            <span>${degree}</span>
                            ${graduationDate ? `<span>${formatDate(graduationDate)}</span>` : ''}
                        </div>
                        <div>${institution}${location ? `, ${location}` : ''}</div>
                        ${gpa ? `<p>Percentage: ${gpa}</p>` : ''}
                    </div>
                `;
            });
        }
        previewEducation2.innerHTML = educationHTML2;
    }
}

function updateExperiencePreview() {
    const experienceFields = document.querySelectorAll('#experience-fields .dynamic-field');
    const isFresher = document.getElementById('isFresher')?.checked || false;
    
    // Template 1
    const previewExperience = document.getElementById('preview-experience');
    if (previewExperience) {
        if (isFresher) {
            previewExperience.innerHTML = '<div class="experience-item">Recent graduate seeking entry-level position</div>';
        } else if (experienceFields.length === 0) {
            previewExperience.innerHTML = '<div class="experience-item"><em>No experience added yet</em></div>';
        } else {
            let experienceHTML1 = '';
            experienceFields.forEach((field, index) => {
                const jobTitle = field.querySelector(`input[id^="jobTitle"]`)?.value || `Position ${index + 1}`;
                const company = field.querySelector(`input[id^="company"]`)?.value || 'Company';
                const location = field.querySelector(`input[id^="workLocation"]`)?.value || '';
                const startDate = field.querySelector(`input[id^="startDate"]`)?.value;
                const endDate = field.querySelector(`input[id^="endDate"]`)?.value;
                const description = field.querySelector(`textarea[id^="jobDescription"]`)?.value || '';
                
                let dateRange = '';
                if (startDate && endDate) {
                    dateRange = `${formatDate(startDate)} - ${formatDate(endDate)}`;
                } else if (startDate) {
                    dateRange = `${formatDate(startDate)} - Present`;
                }
                
                experienceHTML1 += `
                    <div class="experience-item">
                        <div class="item-header">
                            <span>${jobTitle}</span>
                            ${dateRange ? `<span>${dateRange}</span>` : ''}
                        </div>
                        <div>${company}${location ? `, ${location}` : ''}</div>
                        ${description ? `<p>${description}</p>` : ''}
                    </div>
                `;
            });
            previewExperience.innerHTML = experienceHTML1;
        }
    }
    
    // Template 2
    const previewExperience2 = document.getElementById('preview-experience-2');
    if (previewExperience2) {
        if (isFresher) {
            previewExperience2.innerHTML = '<div class="experience-item">Recent graduate seeking entry-level position</div>';
        } else if (experienceFields.length === 0) {
            previewExperience2.innerHTML = '<div class="experience-item"><em>No experience added yet</em></div>';
        } else {
            let experienceHTML2 = '';
            experienceFields.forEach((field, index) => {
                const jobTitle = field.querySelector(`input[id^="jobTitle"]`)?.value || `Position ${index + 1}`;
                const company = field.querySelector(`input[id^="company"]`)?.value || 'Company';
                const location = field.querySelector(`input[id^="workLocation"]`)?.value || '';
                const startDate = field.querySelector(`input[id^="startDate"]`)?.value;
                const endDate = field.querySelector(`input[id^="endDate"]`)?.value;
                const description = field.querySelector(`textarea[id^="jobDescription"]`)?.value || '';
                
                let dateRange = '';
                if (startDate && endDate) {
                    dateRange = `${formatDate(startDate)} - ${formatDate(endDate)}`;
                } else if (startDate) {
                    dateRange = `${formatDate(startDate)} - Present`;
                }
                
                experienceHTML2 += `
                    <div class="experience-item">
                        <div class="item-header">
                            <span>${jobTitle}</span>
                            ${dateRange ? `<span>${dateRange}</span>` : ''}
                        </div>
                        <div>${company}${location ? `, ${location}` : ''}</div>
                        ${description ? `<p>${description}</p>` : ''}
                    </div>
                `;
            });
            previewExperience2.innerHTML = experienceHTML2;
        }
    }
}

function updateCertificationPreview() {
    const certificationFields = document.querySelectorAll('#certification-fields .dynamic-field');
    
    // Template 1
    let certificationHTML1 = '';
    if (certificationFields.length === 0) {
        certificationHTML1 = '<div class="certification-item"><em>No certifications added</em></div>';
    } else {
        certificationFields.forEach((field, index) => {
            const certName = field.querySelector(`input[id^="certName"]`)?.value || `Certification ${index + 1}`;
            const certIssuer = field.querySelector(`input[id^="certIssuer"]`)?.value || '';
            const certDate = field.querySelector(`input[id^="certDate"]`)?.value;
            
            certificationHTML1 += `
                <div class="certification-item">
                    <div class="item-header">
                        <span>${certName}</span>
                        ${certDate ? `<span>${formatDate(certDate)}</span>` : ''}
                    </div>
                    ${certIssuer ? `<div>Issued by: ${certIssuer}</div>` : ''}
                </div>
            `;
        });
    }
    
    // Add/update certifications section in template 1
    const template1 = document.getElementById('template-1');
    if (template1) {
        let certContainer1 = document.getElementById('preview-certifications-1');
        if (certificationHTML1) {
            if (!certContainer1) {
                // Create certifications section
                const lastSection = template1.querySelector('.section:last-of-type');
                if (lastSection) {
                    lastSection.insertAdjacentHTML('afterend', `
                        <div class="section">
                            <h3 class="section-title">Certifications</h3>
                            <div id="preview-certifications-1">${certificationHTML1}</div>
                        </div>
                    `);
                }
            } else {
                certContainer1.innerHTML = certificationHTML1;
            }
        } else if (certContainer1) {
            certContainer1.closest('.section')?.remove();
        }
    }
    
    // Template 2
    let certificationHTML2 = '';
    if (certificationFields.length === 0) {
        certificationHTML2 = '<div class="certification-item"><em>No certifications added</em></div>';
    } else {
        certificationFields.forEach((field, index) => {
            const certName = field.querySelector(`input[id^="certName"]`)?.value || `Certification ${index + 1}`;
            const certIssuer = field.querySelector(`input[id^="certIssuer"]`)?.value || '';
            const certDate = field.querySelector(`input[id^="certDate"]`)?.value;
            
            certificationHTML2 += `
                <div class="certification-item">
                    <div class="item-header">
                        <span>${certName}</span>
                        ${certDate ? `<span>${formatDate(certDate)}</span>` : ''}
                    </div>
                    ${certIssuer ? `<div>Issued by: ${certIssuer}</div>` : ''}
                </div>
            `;
        });
    }
    
    // Add/update certifications section in template 2
    const template2 = document.getElementById('template-2');
    if (template2) {
        let certContainer2 = document.getElementById('preview-certifications-2');
        if (certificationHTML2) {
            if (!certContainer2) {
                // Create certifications section
                const lastSection = template2.querySelector('.section:last-of-type');
                if (lastSection) {
                    lastSection.insertAdjacentHTML('afterend', `
                        <div class="section">
                            <div class="section-title">Certifications</div>
                            <div class="section-content" id="preview-certifications-2">${certificationHTML2}</div>
                        </div>
                    `);
                }
            } else {
                certContainer2.innerHTML = certificationHTML2;
            }
        } else if (certContainer2) {
            certContainer2.closest('.section')?.remove();
        }
    }
}

function updateProjectPreview() {
    const projectFields = document.querySelectorAll('#project-fields .dynamic-field');
    
    // Template 1
    const previewProjects = document.getElementById('preview-projects');
    if (previewProjects) {
        if (projectFields.length === 0) {
            previewProjects.innerHTML = '<div class="project-item"><em>No projects added yet</em></div>';
        } else {
            let projectHTML1 = '';
            projectFields.forEach((field, index) => {
                const projectTitle = field.querySelector(`input[id^="projectTitle"]`)?.value || `Project ${index + 1}`;
                const projectDescription = field.querySelector(`textarea[id^="projectDescription"]`)?.value || '';
                const projectTechnologies = field.querySelector(`input[id^="projectTechnologies"]`)?.value || '';
                const projectLink = field.querySelector(`input[id^="projectLink"]`)?.value || '';
                const projectFeatures = field.querySelector(`textarea[id^="projectFeatures"]`)?.value || '';
                
                projectHTML1 += `
                    <div class="project-item">
                        <div class="item-header">
                            <span>${projectTitle}</span>
                            ${projectLink ? `<span><a href="${projectLink}" target="_blank">View Project</a></span>` : ''}
                        </div>
                        ${projectDescription ? `<p>${projectDescription}</p>` : ''}
                        ${projectTechnologies ? `<p><strong>Technologies:</strong> ${projectTechnologies}</p>` : ''}
                        ${projectFeatures ? `<p><strong>Key Features:</strong> ${projectFeatures}</p>` : ''}
                    </div>
                `;
            });
            previewProjects.innerHTML = projectHTML1;
        }
    }
    
    // Template 2
    const previewProjects2 = document.getElementById('preview-projects-2');
    if (previewProjects2) {
        if (projectFields.length === 0) {
            previewProjects2.innerHTML = '<div class="project-item"><em>No projects added yet</em></div>';
        } else {
            let projectHTML2 = '';
            projectFields.forEach((field, index) => {
                const projectTitle = field.querySelector(`input[id^="projectTitle"]`)?.value || `Project ${index + 1}`;
                const projectDescription = field.querySelector(`textarea[id^="projectDescription"]`)?.value || '';
                const projectTechnologies = field.querySelector(`input[id^="projectTechnologies"]`)?.value || '';
                const projectLink = field.querySelector(`input[id^="projectLink"]`)?.value || '';
                const projectFeatures = field.querySelector(`textarea[id^="projectFeatures"]`)?.value || '';
                
                projectHTML2 += `
                    <div class="project-item">
                        <div class="item-header">
                            <span>${projectTitle}</span>
                            ${projectLink ? `<span><a href="${projectLink}" target="_blank">View Project</a></span>` : ''}
                        </div>
                        ${projectDescription ? `<p>${projectDescription}</p>` : ''}
                        ${projectTechnologies ? `<p><strong>Technologies:</strong> ${projectTechnologies}</p>` : ''}
                        ${projectFeatures ? `<p><strong>Key Features:</strong> ${projectFeatures}</p>` : ''}
                    </div>
                `;
            });
            previewProjects2.innerHTML = projectHTML2;
        }
    }
}

function applyStyles() {
    const fontFamily = document.getElementById('fontFamily')?.value || 'Arial, sans-serif';
    const primaryColor = document.getElementById('primaryColor')?.value || '#007bff';
    const textAlign = document.getElementById('textAlign')?.value || 'left';

    // Apply to all templates
    document.querySelectorAll('.resume-template').forEach(template => {
        if (template) {
            template.style.fontFamily = fontFamily;
            template.style.textAlign = textAlign;
        }
    });

    // Apply header colors
    document.querySelectorAll('.header').forEach(header => {
        if (header) {
            header.style.backgroundColor = primaryColor;
        }
    });

    // Apply section title styles
    document.querySelectorAll('.section-title').forEach(title => {
        if (title) {
            title.style.borderColor = primaryColor;
            title.style.color = primaryColor;
        }
    });

    // Apply skill tag colors
    document.querySelectorAll('.skill-tag').forEach(tag => {
        if (tag) {
            tag.style.backgroundColor = primaryColor + '20'; // 20 = 12% opacity
            tag.style.color = primaryColor;
            tag.style.borderColor = primaryColor;
        }
    });
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================
// Set up event listeners for real-time preview updates
document.addEventListener('input', function(event) {
    if (event.target.matches('input, textarea, select')) {
        updatePreview();
    }
});

// Handle fresher checkbox
const isFresherCheckbox = document.getElementById('isFresher');
if (isFresherCheckbox) {
    isFresherCheckbox.addEventListener('change', function() {
        const experienceFields = document.getElementById('experience-fields');
        const addBtn = document.querySelector('#experience-section .add-btn');
        
        if (experienceFields) {
            experienceFields.style.display = this.checked ? 'none' : 'block';
        }
        if (addBtn) {
            addBtn.style.display = this.checked ? 'none' : 'block';
        }
        
        // Show/hide experience section in tabs
        const experienceTab = document.querySelector('[data-tab="experience"]');
        if (experienceTab) {
            if (this.checked) {
                experienceTab.style.opacity = '0.5';
                experienceTab.style.pointerEvents = 'none';
            } else {
                experienceTab.style.opacity = '1';
                experienceTab.style.pointerEvents = 'auto';
            }
        }
        
        updatePreview();
    });
}

// Style control event listeners
document.getElementById('fontFamily')?.addEventListener('change', updatePreview);
document.getElementById('primaryColor')?.addEventListener('input', updatePreview);
document.getElementById('textAlign')?.addEventListener('change', updatePreview);

// Auto-save form data to localStorage
let autoSaveTimeout;
document.addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        const formData = collectFormData();
        localStorage.setItem('resume_builder_autosave', JSON.stringify({
            data: formData,
            timestamp: new Date().toISOString()
        }));
    }, 1000);
});

// Load auto-saved data
function loadAutoSavedData() {
    const saved = localStorage.getItem('resume_builder_autosave');
    if (saved) {
        try {
            const { data, timestamp } = JSON.parse(saved);
            const time = new Date(timestamp);
            const now = new Date();
            const hoursDiff = (now - time) / (1000 * 60 * 60);
            
            // Only load if saved within 24 hours
            if (hoursDiff < 24) {
                if (confirm('We found an auto-saved resume from ' + time.toLocaleTimeString() + '. Would you like to restore it?')) {
                    restoreFormData(data);
                    showMessage('Auto-saved data restored', 'success');
                }
            }
        } catch (e) {
            console.error('Error loading auto-saved data:', e);
        }
    }
}

// ============================================================================
// PDF DOWNLOAD
// ============================================================================
document.getElementById('download-btn')?.addEventListener('click', async () => {
    const element = document.querySelector('.resume-template.active');
    if (!element) {
        showMessage('No resume template found. Please select a template.', 'error');
        return;
    }
    
    // Validate required fields before download
    if (!validateFormForSave()) {
        showMessage('Please fill in all required fields before downloading.', 'error');
        return;
    }
    
    const options = {
        margin: 10,
        filename: `Resume_${document.getElementById('fullName')?.value || 'MyResume'}.pdf`,
        image: { 
            type: 'jpeg', 
            quality: 0.98 
        },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            scrollX: 0,
            scrollY: 0,
            backgroundColor: '#ffffff'
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait' 
        }
    };
    
    try {
        const downloadBtn = document.getElementById('download-btn');
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        downloadBtn.disabled = true;
        
        showMessage('Generating PDF... This may take a moment.', 'info');
        
        // Add a small delay to ensure DOM is ready
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Generate PDF
        await html2pdf().set(options).from(element).save();
        
        downloadBtn.innerHTML = originalText;
        downloadBtn.disabled = false;
        
        showMessage('PDF downloaded successfully!', 'success');
        
    } catch (error) {
        console.error('PDF generation failed:', error);
        showMessage('Error generating PDF. Please try again.', 'error');
        
        const downloadBtn = document.getElementById('download-btn');
        if (downloadBtn) {
            downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download as PDF';
            downloadBtn.disabled = false;
        }
    }
});

// ============================================================================
// SAVE FUNCTIONALITY - WORKING VERSION
// ============================================================================

// Simple message display
function showMessage(message, type = 'info') {
    // Remove existing message
    const existing = document.querySelector('.message-box');
    if (existing) existing.remove();
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message-box message-${type}`;
    messageEl.textContent = message;
    
    // Add styles
    messageEl.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-family: Arial, sans-serif;
        font-size: 14px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 300px;
        max-width: 500px;
    `;
    
    // Set colors based on type
    if (type === 'success') {
        messageEl.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        messageEl.style.backgroundColor = '#dc3545';
    } else if (type === 'warning') {
        messageEl.style.backgroundColor = '#ffc107';
        messageEl.style.color = '#212529';
    } else {
        messageEl.style.backgroundColor = '#17a2b8';
    }
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: inherit;
        font-size: 20px;
        cursor: pointer;
        position: absolute;
        top: 5px;
        right: 10px;
        padding: 0;
    `;
    closeBtn.onclick = () => messageEl.remove();
    
    messageEl.appendChild(closeBtn);
    document.body.appendChild(messageEl);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageEl.remove(), 300);
        }
    }, 5000);
}

// Validate email
function isValidEmail(email) {
    if (!email) return false;
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Collect form data
function collectFormData() {
    const data = {
        resume_name: document.getElementById('resumeName')?.value || 'My Resume',
        personal_info: {
            full_name: document.getElementById('fullName')?.value || '',
            email: document.getElementById('email')?.value || '',
            phone: document.getElementById('phone')?.value || '',
            address: document.getElementById('address')?.value || '',
            linkedin: document.getElementById('linkedin')?.value || '',
            github: document.getElementById('github')?.value || '',
            portfolio: document.getElementById('portfolio')?.value || '',
            summary: document.getElementById('summary')?.value || ''
        },
        education: [],
        experience: [],
        projects: [],
        skills: {},
        certifications: [],
        template: 'template-1',
        font_family: 'Arial, sans-serif',
        primary_color: '#007bff',
        text_align: 'left'
    };
    
    // Collect education
    document.querySelectorAll('#education-fields .dynamic-field').forEach((field) => {
        data.education.push({
            degree: field.querySelector(`input[id^="degree"]`)?.value || '',
            institution: field.querySelector(`input[id^="institution"]`)?.value || '',
            location: field.querySelector(`input[id^="eduLocation"]`)?.value || '',
            graduation_date: field.querySelector(`input[id^="graduationDate"]`)?.value || '',
            percentage: field.querySelector(`input[id^="gpa"]`)?.value || ''
        });
    });
    
    // Collect experience (if not fresher)
    const isFresher = document.getElementById('isFresher')?.checked;
    if (!isFresher) {
        document.querySelectorAll('#experience-fields .dynamic-field').forEach((field) => {
            data.experience.push({
                job_title: field.querySelector(`input[id^="jobTitle"]`)?.value || '',
                company: field.querySelector(`input[id^="company"]`)?.value || '',
                location: field.querySelector(`input[id^="workLocation"]`)?.value || '',
                start_date: field.querySelector(`input[id^="startDate"]`)?.value || '',
                end_date: field.querySelector(`input[id^="endDate"]`)?.value || '',
                description: field.querySelector(`textarea[id^="jobDescription"]`)?.value || ''
            });
        });
    } else {
        data.experience = [];
    }
    
    // Collect projects
    document.querySelectorAll('#project-fields .dynamic-field').forEach((field) => {
        data.projects.push({
            title: field.querySelector(`input[id^="projectTitle"]`)?.value || '',
            description: field.querySelector(`textarea[id^="projectDescription"]`)?.value || '',
            technologies: field.querySelector(`input[id^="projectTechnologies"]`)?.value || '',
            link: field.querySelector(`input[id^="projectLink"]`)?.value || '',
            features: field.querySelector(`textarea[id^="projectFeatures"]`)?.value || ''
        });
    });
    
    // Collect skills
    const skillsValue = document.getElementById('skills')?.value || '';
    data.skills = {
        technical: skillsValue,
        soft: document.getElementById('softSkills')?.value || '',
        languages: document.getElementById('languages')?.value || ''
    };
    
    // Collect certifications
    document.querySelectorAll('#certification-fields .dynamic-field').forEach((field) => {
        data.certifications.push({
            name: field.querySelector(`input[id^="certName"]`)?.value || '',
            issuer: field.querySelector(`input[id^="certIssuer"]`)?.value || '',
            date: field.querySelector(`input[id^="certDate"]`)?.value || ''
        });
    });
    
    // Get template
    const activeTemplate = document.querySelector('.template-option.active');
    if (activeTemplate) {
        data.template = activeTemplate.getAttribute('data-template');
    }
    
    // Get style settings
    data.font_family = document.getElementById('fontFamily')?.value || 'Arial, sans-serif';
    data.primary_color = document.getElementById('primaryColor')?.value || '#007bff';
    data.text_align = document.getElementById('textAlign')?.value || 'left';
    
    return data;
}

// Restore form data (for auto-save)
function restoreFormData(data) {
    // Restore basic info
    if (document.getElementById('resumeName')) document.getElementById('resumeName').value = data.resume_name || '';
    if (document.getElementById('fullName')) document.getElementById('fullName').value = data.personal_info.full_name || '';
    if (document.getElementById('email')) document.getElementById('email').value = data.personal_info.email || '';
    if (document.getElementById('phone')) document.getElementById('phone').value = data.personal_info.phone || '';
    if (document.getElementById('address')) document.getElementById('address').value = data.personal_info.address || '';
    if (document.getElementById('linkedin')) document.getElementById('linkedin').value = data.personal_info.linkedin || '';
    if (document.getElementById('github')) document.getElementById('github').value = data.personal_info.github || '';
    if (document.getElementById('portfolio')) document.getElementById('portfolio').value = data.personal_info.portfolio || '';
    if (document.getElementById('summary')) document.getElementById('summary').value = data.personal_info.summary || '';
    
    // Restore skills
    if (document.getElementById('skills')) document.getElementById('skills').value = data.skills.technical || '';
    if (document.getElementById('softSkills')) document.getElementById('softSkills').value = data.skills.soft || '';
    if (document.getElementById('languages')) document.getElementById('languages').value = data.skills.languages || '';
    
    // Restore style settings
    if (document.getElementById('fontFamily')) document.getElementById('fontFamily').value = data.font_family || 'Arial, sans-serif';
    if (document.getElementById('primaryColor')) document.getElementById('primaryColor').value = data.primary_color || '#007bff';
    if (document.getElementById('textAlign')) document.getElementById('textAlign').value = data.text_align || 'left';
    
    // Restore template
    const templateBtn = document.querySelector(`[data-template="${data.template}"]`);
    if (templateBtn) {
        templateBtn.click();
    }
    
    updatePreview();
}

// Validate form before saving
function validateFormForSave() {
    const email = document.getElementById('email')?.value;
    const fullName = document.getElementById('fullName')?.value;
    const resumeName = document.getElementById('resumeName')?.value;
    
    // Check required fields
    if (!resumeName?.trim()) {
        showMessage('Please enter a resume name', 'error');
        document.getElementById('resumeName')?.focus();
        return false;
    }
    
    if (!fullName?.trim()) {
        showMessage('Full name is required', 'error');
        document.getElementById('fullName')?.focus();
        return false;
    }
    
    if (!isValidEmail(email)) {
        showMessage('Please enter a valid email address', 'error');
        document.getElementById('email')?.focus();
        return false;
    }
    
    // Check education
    const educationFields = document.querySelectorAll('#education-fields .dynamic-field');
    if (educationFields.length === 0) {
        showMessage('Please add at least one education entry', 'error');
        document.querySelector('[data-tab="education"]')?.click();
        return false;
    }
    
    // Validate each education entry
    for (let field of educationFields) {
        const degree = field.querySelector(`input[id^="degree"]`)?.value || '';
        const institution = field.querySelector(`input[id^="institution"]`)?.value || '';
        
        if (!degree.trim() || !institution.trim()) {
            showMessage('Degree and Institution are required for all education entries', 'error');
            return false;
        }
    }
    
    // Check skills
    const skills = document.getElementById('skills')?.value || '';
    if (!skills.trim()) {
        showMessage('Please add at least one technical skill', 'error');
        document.querySelector('[data-tab="skills"]')?.click();
        return false;
    }
    
    return true;
}

// Tab validation
function validateTab(tabName) {
    switch(tabName) {
        case 'personal':
            const email = document.getElementById('email')?.value;
            const fullName = document.getElementById('fullName')?.value;
            
            if (!fullName?.trim()) {
                showMessage('Full name is required', 'error');
                document.getElementById('fullName')?.focus();
                return false;
            }
            
            if (!isValidEmail(email)) {
                showMessage('Please enter a valid email address', 'error');
                document.getElementById('email')?.focus();
                return false;
            }
            
            return true;
            
        case 'education':
            const educationFields = document.querySelectorAll('#education-fields .dynamic-field');
            if (educationFields.length === 0) {
                showMessage('Please add at least one education entry', 'error');
                return false;
            }
            
            for (let field of educationFields) {
                const degree = field.querySelector(`input[id^="degree"]`)?.value || '';
                const institution = field.querySelector(`input[id^="institution"]`)?.value || '';
                
                if (!degree.trim() || !institution.trim()) {
                    showMessage('Degree and Institution are required', 'error');
                    return false;
                }
            }
            
            return true;
            
        case 'skills':
            const skills = document.getElementById('skills')?.value || '';
            if (!skills.trim()) {
                showMessage('Please add at least one technical skill', 'error');
                document.getElementById('skills')?.focus();
                return false;
            }
            
            return true;
            
        default:
            return true;
    }
}

// Main save function - SIMPLE WORKING VERSION
async function saveResumeToDatabase() {
    console.log('=== SAVE RESUME ===');
    
    // Validate form
    if (!validateFormForSave()) {
        return false;
    }
    
    // Collect data
    const formData = collectFormData();
    console.log('Form data:', formData);
    
    // Show saving status
    const saveBtn = document.getElementById('save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    try {
        // Use ABSOLUTE URL to avoid path issues
        const url = 'http://localhost/dm-resume-builder/backend/api/save_resume.php';
        console.log('Sending to:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status, response.statusText);
        
        // Get response as text first
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Server returned invalid JSON. Please check PHP file.');
        }
        
        // Check if successful
        if (result.success) {
            showMessage('✅ Resume saved successfully!\nResume ID: ' + result.resume_id, 'success');
            
            // Store in localStorage
            localStorage.setItem('last_resume_id', result.resume_id);
            localStorage.setItem('last_resume_name', formData.resume_name);
            
            // Clear auto-save
            localStorage.removeItem('resume_builder_autosave');
            
            return result.resume_id;
        } else {
            throw new Error(result.message || 'Save failed');
        }
        
    } catch (error) {
        console.error('Save error:', error);
        showMessage('❌ Error saving resume: ' + error.message, 'error');
        return false;
        
    } finally {
        // Reset button
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
}

// Test save function
async function testSaveFunction() {
    console.log('=== TEST SAVE ===');
    
    const testData = {
        resume_name: "Test Resume " + new Date().toLocaleTimeString(),
        personal_info: {
            email: "test" + Date.now() + "@example.com",
            full_name: "Test User",
            phone: "123-456-7890"
        },
        education: [{
            degree: "Bachelor of Test",
            institution: "Test University",
            location: "Test City"
        }],
        experience: [],
        projects: [],
        skills: { technical: "JavaScript, HTML, CSS" },
        certifications: [],
        template: "template-1"
    };
    
    showMessage('Sending test data...', 'info');
    
    try {
        const response = await fetch('http://localhost/dm-resume-builder/backend/api/save_resume.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const text = await response.text();
        console.log('Test response:', text);
        
        try {
            const json = JSON.parse(text);
            showMessage(`Test: ${json.success ? '✅ SUCCESS' : '❌ FAILED'} - ${json.message}`, 
                      json.success ? 'success' : 'error');
        } catch (e) {
            showMessage('❌ Test failed - Invalid JSON response', 'error');
        }
    } catch (error) {
        showMessage(`❌ Test error: ${error.message}`, 'error');
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Resume Builder Initializing...');
    
    // Initialize preview
    updatePreview();
    
    // Add event listeners to inputs
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
    
    // Initialize template selection
    const firstTemplate = document.querySelector('.template-option');
    if (firstTemplate && !document.querySelector('.template-option.active')) {
        firstTemplate.classList.add('active');
        const templateId = firstTemplate.getAttribute('data-template');
        document.getElementById(templateId)?.classList.add('active');
    }
    
    // Load auto-saved data
    setTimeout(loadAutoSavedData, 1000);
    
    // Initialize save button
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveResumeToDatabase);
    }
    
    // Add test button
    const actionButtons = document.querySelector('.action-buttons');
    if (actionButtons && !document.getElementById('test-save-btn')) {
        const testBtn = document.createElement('button');
        testBtn.id = 'test-save-btn';
        testBtn.innerHTML = '<i class="fas fa-bug"></i> Test Save';
        testBtn.className = 'btn btn-secondary';
        testBtn.style.marginLeft = '10px';
        testBtn.onclick = testSaveFunction;
        actionButtons.appendChild(testBtn);
    }
    
    console.log('Resume Builder Ready!');
    
    // Show welcome message
    setTimeout(() => {
        showMessage('Welcome to Resume Builder! Fill in your details.', 'info');
    }, 1000);
});

// Make functions available globally
window.addField = addField;
window.removeField = removeField;
window.updatePreview = updatePreview;
window.saveResumeToDatabase = saveResumeToDatabase;
window.testSaveFunction = testSaveFunction;