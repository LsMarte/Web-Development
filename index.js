 // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navLinks = document.querySelector('.nav-links');
        
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenuBtn.innerHTML = navLinks.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        });
        
        // Sticky header on scroll
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            header.classList.toggle('sticky', window.scrollY > 0);
        });
        
        // Scroll reveal animation
        const sr = ScrollReveal({
            origin: 'top',
            distance: '30px',
            duration: 2000,
            reset: false
        });
        
        sr.reveal('.slide-in-left', { origin: 'left' });
        sr.reveal('.slide-in-right', { origin: 'right' });
        sr.reveal('.service-card, .skill-item', { interval: 100 });
        
 document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';
            
            fetch('process_form.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Show success/error message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'form-message';
                messageDiv.textContent = data.message;
                
                // Remove any existing message
                const existingMessage = contactForm.querySelector('.form-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                contactForm.prepend(messageDiv);
                
                if (data.message.includes('Thank you')) {
                    messageDiv.style.color = 'green';
                    contactForm.reset();
                } else {
                    messageDiv.style.color = 'red';
                }
                
                // Scroll to message
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.createElement('div');
                messageDiv.className = 'form-message';
                messageDiv.textContent = 'There was an error submitting the form. Please try again.';
                messageDiv.style.color = 'red';
                
                // Remove any existing message
                const existingMessage = contactForm.querySelector('.form-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                contactForm.prepend(messageDiv);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
        });
    }
});
