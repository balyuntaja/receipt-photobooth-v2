document.addEventListener('alpine:init', () => {
    Alpine.data('zoomWorkspace', (baseWidth, baseHeight) => ({
        zoom: 0.3,
        minZoom: 0.25,
        maxZoom: 2,
        step: 0.1,
        baseWidth: baseWidth,
        baseHeight: baseHeight,

        zoomIn() {
            this.zoom = Math.min(
                this.maxZoom,
                Math.round((this.zoom + this.step) * 100) / 100
            );
        },

        zoomOut() {
            this.zoom = Math.max(
                this.minZoom,
                Math.round((this.zoom - this.step) * 100) / 100
            );
        },

        reset() {
            this.zoom = 1;
        },

        fit() {
            this.$nextTick(() => {
                const ws = this.$refs.workspace;
                const paper = this.$refs.paper;
                if (!ws || !paper) return;

                const wsRect = ws.getBoundingClientRect();
                const paperRect = paper.getBoundingClientRect();

                // paperRect already scaled; normalize to base size
                const baseW = paperRect.width / this.zoom;
                const baseH = paperRect.height / this.zoom;
                const availableW = wsRect.width - 32; // padding
                const availableH = wsRect.height - 120; // toolbar + padding

                const next = Math.min(availableW / baseW, availableH / baseH);
                this.zoom = Math.max(
                    this.minZoom,
                    Math.min(this.maxZoom, Math.round(next * 100) / 100)
                );
            });
        },
    }));

    Alpine.data('frameCanvas', (canvasWidth, canvasHeight) => ({
        draggingId: null,
        canvasEl: null,

        init() {
            this.canvasEl = this.$el;
        },

        startDrag(e, id) {
            if (e.button !== 0) return;
            this.draggingId = id;

            const move = (ev) => this.onMove(ev);
            const up = () => {
                this.draggingId = null;
                document.removeEventListener('mousemove', move);
                document.removeEventListener('mouseup', up);
            };

            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', up);
        },

        onMove(e) {
            if (!this.canvasEl || this.draggingId === null) return;

            const r = this.canvasEl.getBoundingClientRect();
            let x = ((e.clientX - r.left) / r.width) * canvasWidth;
            let y = ((e.clientY - r.top) / r.height) * canvasHeight;

            x = Math.max(0, Math.min(canvasWidth, x));
            y = Math.max(0, Math.min(canvasHeight, y));

            $wire.call('updateSlotPosition', this.draggingId, Math.round(x), Math.round(y));
        },
    }));
});
