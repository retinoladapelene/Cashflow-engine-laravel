export function initMorphObserver() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('morph-visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.morph-element').forEach(el => observer.observe(el));
}

export function initScrollEffects() {
    const navbar = document.querySelector('nav');
    const footer = document.querySelector('footer');

    if (navbar) {
        navbar.classList.remove('bg-white/95', 'dark:bg-slate-900/95', 'backdrop-blur-md', 'border-b', 'border-slate-200', 'dark:border-slate-800', 'shadow-sm');
        navbar.classList.add('bg-transparent', 'border-transparent');
    }



    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;

        if (navbar) {
            if (scrollTop > 50) {
                navbar.classList.add('bg-white/95', 'dark:bg-slate-900/95', 'backdrop-blur-md', 'border-b', 'border-slate-200', 'dark:border-slate-800', 'shadow-sm');
                navbar.classList.remove('bg-transparent', 'border-transparent');
            } else {
                navbar.classList.remove('bg-white/95', 'dark:bg-slate-900/95', 'backdrop-blur-md', 'border-b', 'border-slate-200', 'dark:border-slate-800', 'shadow-sm');
                navbar.classList.add('bg-transparent', 'border-transparent');
            }
        }


    });
}

export function initBackToTop() {
    const btn = document.getElementById('backToTop');
    const footer = document.querySelector('footer');

    if (!btn) return;

    // Use Observer to show/hide based on Footer visibility
    if (footer) {
        const footerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    btn.classList.add('show');
                } else {
                    btn.classList.remove('show');
                }
            });
        }, { threshold: 0.1 });

        footerObserver.observe(footer);
    }

    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}
