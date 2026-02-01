<!DOCTYPE html>
<html lang="id">

<head>
    <title>{{ $project->name }} – Booth</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Load Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style> 
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
            color: #fff;
            font-family: system-ui, sans-serif;
            overflow: hidden;
        }

        .screen {
            display: none;
            height: 100vh;
            width: 100vw;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .screen.active {
            display: flex;
        }

        h1 {
            margin-bottom: .5rem;
        }

        .hint {
            opacity: .6;
            margin-top: .5rem;
        }

        .start-btn {
            margin-top: 2rem;
            padding: 1rem 2.5rem;
            font-size: 1.25rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: #fff;
            color: #000;
        }

        /* FRAME GRID */
        .frames {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
            width: 80%;
            max-width: 1000px;
        }

        .frame-card {
            background: #111;
            border: 2px solid transparent;
            border-radius: 16px;
            padding: 1rem;
            color: white;
            cursor: pointer;
            transition: border .2s ease, transform .1s ease;
        }

        .frame-card:hover {
            border-color: #fff;
            transform: scale(1.03);
        }

        .frame-card img {
            width: 100%;
            border-radius: 12px;
            aspect-ratio: 3 / 5;
            object-fit: cover;
            margin-bottom: .5rem;
        }
    </style>
</head>

<body>

    {{-- =========================
    HOMESCREEN
    ========================== --}}
    <div id="screen-idle" class="screen active">
        <h1>{{ $project->name }}</h1>
        <p class="hint">Tap to start your photo session</p>

        <button class="start-btn" onclick="startSession()">
            Start
        </button>
    </div>

    {{-- =========================
    FRAME SELECTION
    ========================== --}}
    <div id="screen-frame" class="screen">
        <h1>Pilih Frame</h1>
        <p class="hint">Pilih desain receipt favoritmu</p>

        <div class="frames">
            @foreach ($frames as $frame)
                <button class="frame-card" onclick="selectFrame('{{ $frame->id }}')">
                    <img src="{{ asset('storage/' . $frame->preview_image) }}">
                    <div>{{ $frame->name }}</div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- =========================
    PLACEHOLDER NEXT STEP
    ========================== --}}
    <div id="screen-capture" class="screen">
        <h1>Camera Ready</h1>
        <p class="hint">Next step: camera capture</p>
    </div>

    <script>
        /**
         * =========================
         * KIOSK STATE MACHINE
         * =========================
         */
        const Kiosk = {
            state: '{{ $initialState ?? 'IDLE' }}',

            setState(newState) {
                console.log('Kiosk state:', newState);
                this.state = newState;
                this.render();
            },

            render() {
                document
                    .querySelectorAll('.screen')
                    .forEach(el => el.classList.remove('active'));

                switch (this.state) {
                    case 'IDLE':
                        show('screen-idle');
                        break;

                    case 'FRAME':
                        show('screen-frame');
                        break;

                    case 'CAPTURE':
                        show('screen-capture');
                        break;
                }
            }
        };

        function show(id) {
            document.getElementById(id)?.classList.add('active');
        }

        function startSession() {
            Kiosk.setState('FRAME');
        }

        function selectFrame(frameId) {
            console.log('Selected frame:', frameId);

            fetch(`/booth/session/{{ $session->id }}/frame`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ frame_id: frameId }),
            });

            Kiosk.setState('CAPTURE');
        }

        // Force fullscreen on first interaction (tablet / kiosk)
        document.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => { });
            }
        }, { once: true });
    </script>

</body>

</html>