/* ===========================================
   SLIDE PRESENTATION CONTROLLER
   Fixed 1920x1080 stage, keyboard / wheel / touch navigation,
   URL hash (#5) deep links, F = fullscreen, E = inline text edit.
   =========================================== */
class SlidePresentation {
    constructor() {
        this.slides = [...document.querySelectorAll('.slide')];
        this.stage = document.getElementById('deckStage');
        this.progress = document.querySelector('.progress');
        this.counter = document.querySelector('.counter');
        this.current = 0;
        this.setupStageScale();
        this.setupKeyboardNav();
        this.setupWheelNav();
        this.setupTouchNav();
        const fromHash = parseInt(location.hash.replace('#', ''), 10);
        this.show(Number.isFinite(fromHash) ? fromHash - 1 : 0);
        // ?static=1 shows every element in its final state (used for QA screenshots)
        if (new URLSearchParams(location.search).has('static')) document.documentElement.classList.add('static');
    }

    setupStageScale() {
        const scale = () => {
            const f = Math.min(window.innerWidth / 1920, window.innerHeight / 1080);
            const x = (window.innerWidth - 1920 * f) / 2;
            const y = (window.innerHeight - 1080 * f) / 2;
            this.stage.style.transform = `translate(${x}px, ${y}px) scale(${f})`;
        };
        scale();
        window.addEventListener('resize', scale);
    }

    setupKeyboardNav() {
        document.addEventListener('keydown', (e) => {
            if (e.target.isContentEditable) return;
            const next = ['ArrowRight', 'ArrowDown', 'PageDown', ' ', 'Enter'];
            const prev = ['ArrowLeft', 'ArrowUp', 'PageUp', 'Backspace'];
            if (next.includes(e.key)) { e.preventDefault(); this.show(this.current + 1); }
            else if (prev.includes(e.key)) { e.preventDefault(); this.show(this.current - 1); }
            else if (e.key === 'Home') this.show(0);
            else if (e.key === 'End') this.show(this.slides.length - 1);
            else if (e.key === 'f' || e.key === 'F') {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen?.();
                else document.exitFullscreen?.();
            }
        });
    }

    setupWheelNav() {
        let lock = false;
        window.addEventListener('wheel', (e) => {
            if (lock || Math.abs(e.deltaY) < 30) return;
            lock = true;
            this.show(this.current + (e.deltaY > 0 ? 1 : -1));
            setTimeout(() => { lock = false; }, 700);
        }, { passive: true });
    }

    setupTouchNav() {
        let x0 = null;
        window.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
        window.addEventListener('touchend', (e) => {
            if (x0 === null) return;
            const dx = e.changedTouches[0].clientX - x0;
            if (Math.abs(dx) > 50) this.show(this.current + (dx < 0 ? 1 : -1));
            x0 = null;
        });
    }

    show(i) {
        this.current = Math.max(0, Math.min(i, this.slides.length - 1));
        this.slides.forEach((s, k) => {
            s.classList.toggle('active', k === this.current);
            s.classList.toggle('visible', k === this.current);
        });
        if (this.progress) this.progress.style.width = `${((this.current + 1) / this.slides.length) * 100}%`;
        if (this.counter) this.counter.textContent = `${this.current + 1} / ${this.slides.length}`;
        history.replaceState(null, '', `#${this.current + 1}`);
    }
}

/* ===========================================
   INLINE EDITING
   Hover the top-left corner or press E; Ctrl+S saves a copy of the file.
   No localStorage: the deck embeds images, which would overflow it.
   =========================================== */
class InlineEditor {
    constructor() {
        this.isActive = false;
        this.toggle = document.getElementById('editToggle');
        const hot = document.querySelector('.edit-hotzone');
        let t = null;
        const showBtn = () => { clearTimeout(t); this.toggle.classList.add('show'); };
        const hideBtn = () => { t = setTimeout(() => { if (!this.isActive) this.toggle.classList.remove('show'); }, 400); };
        hot.addEventListener('mouseenter', showBtn);
        hot.addEventListener('mouseleave', hideBtn);
        this.toggle.addEventListener('mouseenter', showBtn);
        this.toggle.addEventListener('mouseleave', hideBtn);
        hot.addEventListener('click', () => this.toggleMode());
        this.toggle.addEventListener('click', () => this.toggleMode());
        document.addEventListener('keydown', (e) => {
            if ((e.key === 'e' || e.key === 'E') && !e.target.isContentEditable && !e.ctrlKey && !e.metaKey) this.toggleMode();
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); this.save(); }
        });
    }
    editable() { return document.querySelectorAll('.slide h1, .slide h2, .slide h3, .slide p, .slide li, .slide td, .slide th, .slide .label, .slide .kicker'); }
    toggleMode() {
        this.isActive = !this.isActive;
        document.documentElement.classList.toggle('editing', this.isActive);
        this.toggle.classList.toggle('active', this.isActive);
        this.toggle.textContent = this.isActive ? 'แก้ไขอยู่ · กด E เพื่อจบ' : 'แก้ไขข้อความ (E)';
        this.editable().forEach((el) => { el.contentEditable = this.isActive ? 'true' : 'false'; });
    }
    save() {
        if (this.isActive) this.toggleMode();
        const html = '<!DOCTYPE html>\n' + document.documentElement.outerHTML;
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([html], { type: 'text/html' }));
        a.download = (document.title || 'slides').replace(/\s+/g, '-') + '.html';
        a.click();
    }
}

document.fonts.ready.then(() => { window.deck = new SlidePresentation(); new InlineEditor(); });
