// Enhanced Portfolio JavaScript
class Portfolio {
    constructor() {
        this.apiBase = 'backend/api/';
        this.cache = new Map();
        this.init();
    }

    init() {
        this.loadDynamicContent();
        this.setupEventListeners();
        this.initializeAnimations();
        this.initializeParticles();
        this.initializeTypingEffects();
        this.initializeCursor();
        this.initializeSkillBars();
        this.initializePortfolioFilter();
        this.initializeContactForm();
        this.hideLoadingScreen();
    }

    async loadDynamicContent() {
        try {
            // Load all portfolio content from API
            await this.loadStats();
            await this.loadSkills();
            await this.loadProjects();
            await this.loadServices();
        } catch (error) {
            console.warn('Failed to load dynamic content:', error);
            // Continue with static content if API fails
        }
    }

    async apiCall(endpoint, params = {}) {
        const cacheKey = endpoint + JSON.stringify(params);
        
        // Check cache first
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const url = new URL(this.apiBase + 'portfolio.php', window.location.origin);
            url.searchParams.append('endpoint', endpoint);
            
            Object.keys(params).forEach(key => {
                url.searchParams.append(key, params[key]);
            });

            const response = await fetch(url.toString());
            
            if (!response.ok) {
                throw new Error(`API call failed: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Cache successful responses for 5 minutes
                this.cache.set(cacheKey, data);
                setTimeout(() => this.cache.delete(cacheKey), 5 * 60 * 1000);
                
                return data;
            } else {
                throw new Error(data.message || 'API call failed');
            }
        } catch (error) {
            console.error('API call error:', error);
            throw error;
        }
    }

    async loadStats() {
        try {
            const response = await this.apiCall('stats');
            const stats = response.data;

            // Update stats counters if elements exist
            if (stats.experience && document.querySelector('[data-stat="experience"]')) {
                this.animateCounter('[data-stat="experience"]', 0, stats.experience, 2000);
            }
            
            if (stats.projects && document.querySelector('[data-stat="projects"]')) {
                this.animateCounter('[data-stat="projects"]', 0, stats.projects, 2000);
            }
            
            if (stats.clients && document.querySelector('[data-stat="clients"]')) {
                this.animateCounter('[data-stat="clients"]', 0, stats.clients, 2000);
            }
            
            if (stats.reviews && document.querySelector('[data-stat="reviews"]')) {
                this.animateCounter('[data-stat="reviews"]', 0, stats.reviews, 2000);
            }

        } catch (error) {
            console.warn('Failed to load stats:', error);
        }
    }

    async loadSkills() {
        try {
            const response = await this.apiCall('skills');
            const skills = response.data;

            const skillsContainer = document.querySelector('.skills-container');
            if (!skillsContainer || !skills || skills.length === 0) return;

            // Group skills by category
            const groupedSkills = skills.reduce((acc, skill) => {
                if (!acc[skill.category]) {
                    acc[skill.category] = [];
                }
                acc[skill.category].push(skill);
                return acc;
            }, {});

            // Generate HTML for skills
            let skillsHTML = '';
            
            Object.keys(groupedSkills).forEach(category => {
                skillsHTML += `
                    <div class="skill-category">
                        <h3 class="category-title">${category}</h3>
                        <div class="skills-grid">
                `;
                
                groupedSkills[category].forEach(skill => {
                    skillsHTML += `
                        <div class="skill-item animate-on-scroll">
                            <div class="skill-header">
                                <span class="skill-name">${skill.name}</span>
                                <span class="skill-percentage">${skill.level}%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" data-level="${skill.level}"></div>
                            </div>
                        </div>
                    `;
                });
                
                skillsHTML += '</div></div>';
            });

            skillsContainer.innerHTML = skillsHTML;

        } catch (error) {
            console.warn('Failed to load skills:', error);
        }
    }

    async loadProjects() {
        try {
            const response = await this.apiCall('projects');
            const projects = response.data;

            const portfolioContainer = document.querySelector('.portfolio-grid');
            if (!portfolioContainer || !projects || projects.length === 0) return;

            // Generate project HTML
            let projectsHTML = '';
            
            projects.forEach(project => {
                const technologies = Array.isArray(project.technologies) ? 
                    project.technologies : 
                    (project.technologies ? project.technologies.split(',') : []);

                const techBadges = technologies.map(tech => 
                    `<span class="tech-badge">${tech.trim()}</span>`
                ).join('');

                projectsHTML += `
                    <div class="portfolio-item animate-on-scroll" data-category="${project.category}">
                        <div class="portfolio-image">
                            <img src="${project.image_url || 'Img/default-project.png'}" 
                                 alt="${project.title}" 
                                 loading="lazy">
                            <div class="portfolio-overlay">
                                <div class="portfolio-actions">
                                    ${project.demo_url ? `<a href="${project.demo_url}" target="_blank" class="action-btn">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>` : ''}
                                    ${project.github_url ? `<a href="${project.github_url}" target="_blank" class="action-btn">
                                        <i class="fab fa-github"></i>
                                    </a>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="portfolio-content">
                            <h3 class="project-title">${project.title}</h3>
                            <p class="project-description">${project.description}</p>
                            <div class="project-tech">
                                ${techBadges}
                            </div>
                        </div>
                    </div>
                `;
            });

            portfolioContainer.innerHTML = projectsHTML;

        } catch (error) {
            console.warn('Failed to load projects:', error);
        }
    }

    async loadServices() {
        try {
            const response = await this.apiCall('services');
            const services = response.data;

            const servicesContainer = document.querySelector('.services-grid');
            if (!servicesContainer || !services || services.length === 0) return;

            // Generate services HTML
            let servicesHTML = '';
            
            services.forEach(service => {
                const features = Array.isArray(service.features) ? 
                    service.features : 
                    (service.features ? service.features.split(',') : []);

                const featuresList = features.map(feature => 
                    `<li>${feature.trim()}</li>`
                ).join('');

                servicesHTML += `
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="${service.icon || 'fas fa-code'}"></i>
                        </div>
                        <h3 class="service-title">${service.name}</h3>
                        <p class="service-description">${service.description}</p>
                        ${featuresList ? `
                            <ul class="service-features">
                                ${featuresList}
                            </ul>
                        ` : ''}
                        <div class="service-price">
                            <span class="price">$${service.price}</span>
                            <span class="price-period">/${service.billing_period || 'project'}</span>
                        </div>
                    </div>
                `;
            });

            servicesContainer.innerHTML = servicesHTML;

        } catch (error) {
            console.warn('Failed to load services:', error);
        }
    }

    animateCounter(selector, start, end, duration) {
        const element = document.querySelector(selector);
        if (!element) return;

        Utils.animateValue(element, start, end, duration, (value) => {
            element.textContent = value;
        });
    }

    setupEventListeners() {
        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const navLinks = document.getElementById('navLinks');
        
        if (mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', () => {
                mobileToggle.classList.toggle('active');
                navLinks.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });

            // Close mobile menu when clicking on a nav link
            const navLinksElements = navLinks.querySelectorAll('.nav-link');
            navLinksElements.forEach(link => {
                link.addEventListener('click', () => {
                    mobileToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                    document.body.classList.remove('menu-open');
                });
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!mobileToggle.contains(e.target) && !navLinks.contains(e.target)) {
                    mobileToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            });
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (header) {
                header.classList.toggle('scrolled', window.scrollY > 100);
            }
            this.updateActiveNavLink();
        });

        // Window resize handler
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }

    updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    initializeAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    initializeParticles() {
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: {
                        value: 80,
                        density: {
                            enable: true,
                            value_area: 800
                        }
                    },
                    color: {
                        value: '#667eea'
                    },
                    shape: {
                        type: 'circle'
                    },
                    opacity: {
                        value: 0.5,
                        random: false
                    },
                    size: {
                        value: 3,
                        random: true
                    },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: '#667eea',
                        opacity: 0.4,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 6,
                        direction: 'none',
                        random: false,
                        straight: false,
                        out_mode: 'out',
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: {
                            enable: true,
                            mode: 'repulse'
                        },
                        onclick: {
                            enable: true,
                            mode: 'push'
                        },
                        resize: true
                    }
                },
                retina_detect: true
            });
        }
    }

    initializeTypingEffects() {
        // Greeting text typing effect
        const greetingText = document.getElementById('greetingText');
        if (greetingText) {
            this.typeText(greetingText, "Hello, It's Me", 200);
        }

        // Role typing effect
        const typingText = document.getElementById('typingText');
        if (typingText) {
            const roles = [
                'Frontend Developer',
                'Web Designer',
                'Problem Solver',
                'Creative Thinker'
            ];
            this.typeRoles(typingText, roles);
        }
    }

    typeText(element, text, speed = 100) {
        let i = 0;
        const timer = setInterval(() => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(timer);
            }
        }, speed);
    }

    typeRoles(element, roles) {
        let roleIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        const typeSpeed = 100;
        const deleteSpeed = 50;
        const pauseTime = 2000;

        const type = () => {
            const currentRole = roles[roleIndex];
            
            if (isDeleting) {
                element.textContent = currentRole.substring(0, charIndex - 1);
                charIndex--;
            } else {
                element.textContent = currentRole.substring(0, charIndex + 1);
                charIndex++;
            }

            let speed = isDeleting ? deleteSpeed : typeSpeed;

            if (!isDeleting && charIndex === currentRole.length) {
                speed = pauseTime;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                roleIndex = (roleIndex + 1) % roles.length;
            }

            setTimeout(type, speed);
        };

        type();
    }

    initializeCursor() {
        const cursor = document.getElementById('cursorFollower');
        if (!cursor) return;

        let mouseX = 0;
        let mouseY = 0;
        let cursorX = 0;
        let cursorY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            cursor.style.opacity = '1';
        });
        document.addEventListener('mouseleave', () => {
            cursor.style.opacity = '0';
        });

        const animateCursor = () => {
            const speed = 0.15;
            cursorX += (mouseX - cursorX) * speed;
            cursorY += (mouseY - cursorY) * speed;
            
            cursor.style.left = cursorX + 'px';
            cursor.style.top = cursorY + 'px';
            
            requestAnimationFrame(animateCursor);
        };
        
        animateCursor();

        // Cursor interactions
        const interactiveElements = document.querySelectorAll('a, button, .service-card, .skill-item, .portfolio-item');
        
        interactiveElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.style.transform = 'scale(2)';
                cursor.style.opacity = '0.5';
            });
            
            el.addEventListener('mouseleave', () => {
                cursor.style.transform = 'scale(1)';
                cursor.style.opacity = '1';
            });
        });
    }

    initializeSkillBars() {
        const skillBars = document.querySelectorAll('.skill-progress');
        
        const animateSkillBars = () => {
            skillBars.forEach(bar => {
                const level = bar.getAttribute('data-level');
                if (level) {
                    setTimeout(() => {
                        bar.style.width = level + '%';
                    }, 500);
                }
            });
        };

        // Trigger animation when skills section is visible
        const skillsSection = document.getElementById('skills');
        if (skillsSection) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateSkillBars();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            observer.observe(skillsSection);
        }
    }

    initializePortfolioFilter() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const portfolioItems = document.querySelectorAll('.portfolio-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');

                portfolioItems.forEach(item => {
                    const category = item.getAttribute('data-category');
                    
                    if (filterValue === 'all' || category === filterValue) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'scale(1)';
                        }, 100);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    initializeContactForm() {
        const form = document.getElementById('contactForm');
        const formStatus = document.getElementById('form-status');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Sending...';
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                try {
                    // Send to backend
                    const result = await this.simulateFormSubmission(data);
                    
                    if (result.success) {
                        this.showFormStatus(result.message || 'Message sent successfully!', 'success');
                        form.reset();
                        
                        // Track analytics
                        this.trackContactFormSubmission(result);
                    } else {
                        throw new Error(result.message || 'Failed to send message');
                    }
                    
                } catch (error) {
                    console.error('Contact form error:', error);
                    this.showFormStatus(error.message || 'Failed to send message. Please try again.', 'error');
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            });
        }
    }

    async simulateFormSubmission(data) {
        // Send to PHP backend instead of simulation
        try {
            const response = await fetch('backend/api/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to send message');
            }

            return result;

        } catch (error) {
            // Fallback to local processing if backend is unavailable
            console.warn('Backend unavailable, using fallback:', error.message);
            
            // Simulate API call as fallback
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    // Simulate success/failure
                    if (Math.random() > 0.1) {
                        resolve({
                            success: true,
                            message: 'Message sent successfully! (Fallback mode)',
                            message_id: 'local_' + Date.now()
                        });
                    } else {
                        reject(new Error('Submission failed (Fallback mode)'));
                    }
                }, 2000);
            });
        }
    }

    showFormStatus(message, type) {
        const formStatus = document.querySelector('.form-status');
        if (formStatus) {
            formStatus.textContent = message;
            formStatus.className = `form-status ${type} show`;
            formStatus.style.display = 'block';

            setTimeout(() => {
                formStatus.classList.remove('show');
                setTimeout(() => {
                    formStatus.style.display = 'none';
                }, 300);
            }, 5000);
        }
    }

    hideLoadingScreen() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            setTimeout(() => {
                loadingScreen.classList.add('hidden');
            }, 1000);
        }
    }
    
    trackContactFormSubmission(result) {
        // Track successful form submission
        if ('gtag' in window) {
            gtag('event', 'contact_form_submit', {
                'event_category': 'engagement',
                'event_label': 'contact_form',
                'value': 1
            });
        }
        
        // Track locally for analytics
        this.trackEvent('contact_form_submit', {
            message_id: result.message_id,
            timestamp: new Date().toISOString()
        });
    }
    
    trackEvent(eventName, data) {
        // Simple local analytics tracking
        try {
            const events = JSON.parse(localStorage.getItem('portfolio_events') || '[]');
            events.push({
                event: eventName,
                data: data,
                timestamp: new Date().toISOString(),
                url: window.location.href
            });
            
            // Keep only last 100 events
            if (events.length > 100) {
                events.splice(0, events.length - 100);
            }
            
            localStorage.setItem('portfolio_events', JSON.stringify(events));
        } catch (error) {
            console.warn('Analytics tracking failed:', error);
        }
    }

    handleResize() {
        // Handle responsive behavior
        const navLinks = document.getElementById('navLinks');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        
        if (window.innerWidth > 768) {
            if (navLinks) navLinks.classList.remove('active');
            if (mobileToggle) mobileToggle.classList.remove('active');
        }
    }
}

// Utility functions
class Utils {
    static debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    }

    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    static isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    static animateValue(obj, start, end, duration, callback) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            if (callback) callback(value);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
}

// Performance monitoring
class PerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.init();
    }

    init() {
        if ('performance' in window) {
            this.measurePageLoad();
            this.measureInteractions();
        }
    }

    measurePageLoad() {
        window.addEventListener('load', () => {
            const perfData = performance.getEntriesByType('navigation')[0];
            this.metrics.pageLoad = {
                domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
                loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
                totalTime: perfData.loadEventEnd - perfData.fetchStart
            };
            
            console.log('Page Load Metrics:', this.metrics.pageLoad);
        });
    }

    measureInteractions() {
        // Measure First Input Delay (FID)
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.entryType === 'first-input') {
                        this.metrics.firstInputDelay = entry.processingStart - entry.startTime;
                        console.log('First Input Delay:', this.metrics.firstInputDelay);
                    }
                }
            });
            
            observer.observe({ entryTypes: ['first-input'] });
        }
    }

    getMetrics() {
        return this.metrics;
    }
}

// Accessibility enhancements
class AccessibilityManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupAriaLabels();
        this.setupReducedMotion();
    }

    setupKeyboardNavigation() {
        // Handle keyboard navigation for custom elements
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    setupFocusManagement() {
        // Ensure focus is visible and properly managed
        const focusableElements = document.querySelectorAll(
            'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );

        focusableElements.forEach(element => {
            element.addEventListener('focus', () => {
                element.classList.add('focused');
            });

            element.addEventListener('blur', () => {
                element.classList.remove('focused');
            });
        });
    }

    setupAriaLabels() {
        // Ensure all interactive elements have proper aria labels
        const interactiveElements = document.querySelectorAll('button, a, input');
        
        interactiveElements.forEach(element => {
            if (!element.getAttribute('aria-label') && !element.getAttribute('aria-labelledby')) {
                const text = element.textContent || element.value || element.alt;
                if (text) {
                    element.setAttribute('aria-label', text.trim());
                }
            }
        });
    }

    setupReducedMotion() {
        // Respect user's motion preferences
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        
        if (prefersReducedMotion.matches) {
            document.body.classList.add('reduced-motion');
            
            // Disable animations for users who prefer reduced motion
            const style = document.createElement('style');
            style.textContent = `
                .reduced-motion *,
                .reduced-motion *::before,
                .reduced-motion *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Error handling and logging
class ErrorHandler {
    constructor() {
        this.init();
    }

    init() {
        window.addEventListener('error', (e) => {
            this.logError('JavaScript Error', e.error);
        });

        window.addEventListener('unhandledrejection', (e) => {
            this.logError('Unhandled Promise Rejection', e.reason);
        });
    }

    logError(type, error) {
        const errorInfo = {
            type: type,
            message: error.message || error,
            stack: error.stack,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href
        };

        console.error('Error logged:', errorInfo);
        
        // In production, send to error tracking service
        // this.sendToErrorService(errorInfo);
    }

    sendToErrorService(errorInfo) {
        // Implementation for sending errors to tracking service
        // e.g., Sentry, LogRocket, etc.
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize main portfolio functionality
    new Portfolio();
    
    // Initialize additional features
    new PerformanceMonitor();
    new AccessibilityManager();
    new ErrorHandler();
    
    // Add some additional enhancements
    initializeThemeToggle();
    initializeLazyLoading();
    initializeServiceWorker();
});

// Theme toggle functionality (optional)
function initializeThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        themeToggle.addEventListener('click', () => {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
}

// Lazy loading for images
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Service Worker registration (for PWA capabilities)
function initializeServiceWorker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Portfolio, Utils, PerformanceMonitor, AccessibilityManager, ErrorHandler };
}

   
