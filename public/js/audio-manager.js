window.AudioManager = {
    audioCtx: null,
    muted: localStorage.getItem('iks_muted') === 'true',

    init() {
        if (!this.audioCtx) {
            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
    },

    toggleMute() {
        this.muted = !this.muted;
        localStorage.setItem('iks_muted', this.muted);
        return this.muted;
    },

    isMuted() {
        return this.muted;
    },

    play(type) {
        if (this.muted) return;
        this.init();
        
        // Resume if suspended (browser policy for Web Audio API)
        if (this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }

        const t = this.audioCtx.currentTime;
        const osc = this.audioCtx.createOscillator();
        const gain = this.audioCtx.createGain();
        
        osc.connect(gain);
        gain.connect(this.audioCtx.destination);

        if (type === 'pop') {
            // Satisfying UI Pop
            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, t);
            osc.frequency.exponentialRampToValueAtTime(300, t + 0.1);
            gain.gain.setValueAtTime(0.5, t);
            gain.gain.exponentialRampToValueAtTime(0.01, t + 0.1);
            osc.start(t);
            osc.stop(t + 0.1);
        } else if (type === 'success') {
            // Success Chime (two notes)
            osc.type = 'sine';
            osc.frequency.setValueAtTime(523.25, t); // C5
            osc.frequency.setValueAtTime(659.25, t + 0.1); // E5
            
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.2, t + 0.05);
            gain.gain.setValueAtTime(0.2, t + 0.1);
            gain.gain.linearRampToValueAtTime(0, t + 0.5);
            
            osc.start(t);
            osc.stop(t + 0.5);
        } else if (type === 'error') {
            // Error Thud
            osc.type = 'square';
            osc.frequency.setValueAtTime(150, t);
            osc.frequency.exponentialRampToValueAtTime(50, t + 0.2);
            
            const filter = this.audioCtx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(400, t);
            
            osc.disconnect();
            osc.connect(filter);
            filter.connect(gain);
            
            gain.gain.setValueAtTime(0.2, t);
            gain.gain.exponentialRampToValueAtTime(0.01, t + 0.2);
            
            osc.start(t);
            osc.stop(t + 0.2);
        } else if (type === 'start') {
            // Exciting Start swoosh
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(300, t);
            osc.frequency.linearRampToValueAtTime(880, t + 0.2);
            
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.2, t + 0.1);
            gain.gain.linearRampToValueAtTime(0, t + 0.3);
            
            osc.start(t);
            osc.stop(t + 0.3);
        }
    }
};

// Dispatch a custom event so Alpine can react to mute changes if needed
window.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('audio-manager-ready'));
});
