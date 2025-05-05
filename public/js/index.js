document.addEventListener('DOMContentLoaded', function() {
    // Animación del hero
    setTimeout(() => {
        document.querySelector('.hero-title').style.animation = 'fadeInUp 1s forwards';
    }, 300);
    
    setTimeout(() => {
        document.querySelector('.hero-text').style.animation = 'fadeInUp 1s forwards';
    }, 600);
    
    setTimeout(() => {
        document.querySelector('.hero .btn').style.animation = 'fadeInUp 1s forwards';
    }, 900);
    
    // Animación de las características
    const observerFeatures = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 200);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.feature-box').forEach(box => {
        observerFeatures.observe(box);
    });
    
    // Animación de las motos
    const observerBikes = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    
                    // Reemplazar el loader con la imagen real después de una animación
                    const loader = entry.target.querySelector('.bike-loader');
                    if (loader) {
                        setTimeout(() => {
                            const img = document.createElement('img');
                            img.src = `/api/placeholder/400/250`;
                            img.classList.add('card-img-top', 'bike-img');
                            img.alt = entry.target.querySelector('.card-title').textContent;
                            loader.parentNode.replaceChild(img, loader);
                        }, 1000);
                    }
                }, index * 200);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.bike-card').forEach(card => {
        observerBikes.observe(card);
    });
    
    // Animación del CTA
    const observerCTA = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 300);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.cta-title, .cta-text, .cta-section .btn').forEach(element => {
        observerCTA.observe(element);
    });
    
    // Efecto de paralaje para el hero
    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY;
        const heroSection = document.querySelector('.hero');
        if (heroSection) {
            heroSection.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
        }
    });
});