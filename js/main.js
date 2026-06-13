/**
 * Main JavaScript - 心理健康测评中心
 */

// ========== Mobile Menu ==========
function toggleMobileMenu() {
    const links = document.querySelector('.nav-links');
    const btn = document.querySelector('.mobile-menu-btn');
    links.classList.toggle('show');
    btn.classList.toggle('active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const nav = document.querySelector('.navbar');
    const links = document.querySelector('.nav-links');
    const btn = document.querySelector('.mobile-menu-btn');
    if (!nav.contains(e.target) && links.classList.contains('show')) {
        links.classList.remove('show');
        btn.classList.remove('active');
    }
});

// ========== Smooth Scroll for Anchor Links ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        const target = document.querySelector(targetId);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ========== Intersection Observer for Animations ==========
if ('IntersectionObserver' in window) {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const animateOnScroll = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                animateOnScroll.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe assessment cards
    document.querySelectorAll('.assessment-card').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = `opacity 0.5s ${i * 0.08}s ease, transform 0.5s ${i * 0.08}s ease`;
        animateOnScroll.observe(el);
    });

    // Observe feature items
    document.querySelectorAll('.feature-item').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = `opacity 0.4s ${i * 0.1}s ease, transform 0.4s ${i * 0.1}s ease`;
        animateOnScroll.observe(el);
    });
}

// ========== Hero Card Float Animation ==========
const heroCards = document.querySelectorAll('.hero-card');
if (heroCards.length) {
    let floatFrame = 0;
    function animateHeroCards() {
        heroCards.forEach((card, i) => {
            const offset = Math.sin(floatFrame * 0.02 + i * 1.2) * 4;
            card.style.transform = card.classList.contains('hero-card-1') 
                ? `rotate(${-3 + offset * 0.1}deg) translateY(${offset}px)`
                : card.classList.contains('hero-card-2')
                    ? `rotate(${1 + offset * 0.08}deg) translateY(${offset * 0.8}px)`
                    : `rotate(${-1 + offset * 0.05}deg) translateY(${offset * 0.6}px)`;
        });
        floatFrame++;
        requestAnimationFrame(animateHeroCards);
    }
    animateHeroCards();
}

// ========== Tip Cards Hover Effect ==========
document.querySelectorAll('.tip-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.querySelector('.tip-icon-wrap').style.transform = 'scale(1.1) rotate(5deg)';
        this.querySelector('.tip-icon-wrap').style.transition = 'transform 0.3s';
    });
    card.addEventListener('mouseleave', function() {
        this.querySelector('.tip-icon-wrap').style.transform = 'scale(1) rotate(0deg)';
    });
});

// ========== Utility Functions ==========
function debounce(fn, delay) {
    let timer = null;
    return function() {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, arguments), delay);
    };
}

// Console welcome message
console.log(
    '%c 🧠 MindCheck %c 心理健康测评中心 ',
    'background:#6366F1;color:white;padding:4px 8px;border-radius:4px 0 0 4px;font-weight:bold;',
    'background:#7c3aed;color:white;padding:4px 8px;border-radius:0 4px 4px 0;'
);

console.log(
    '%c 提示：本系统仅供自我参考，不能替代专业医疗诊断。如有需要请咨询专业医师。',
    'color:#94a3b8;font-size:12px;'
);
