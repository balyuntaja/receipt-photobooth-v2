{{--
    Welcome Screen
    ==============
    Renders the welcome screen with components from the database.
    Variables:
      - $welcomeComponents: Collection of WelcomeScreenComponent
      - $project: Project model
--}}

@php
    $backgroundComponent = $welcomeComponents->firstWhere('type', 'background');
    $hasBackground = $backgroundComponent && isset($backgroundComponent->content['path']);
    $fallbackBgColor = $project->welcome_background_color ?? null;
    $screenStyle = '';
    if (!$hasBackground && $fallbackBgColor) {
        $screenStyle = 'background-color:' . e($fallbackBgColor) . ';';
    }
@endphp

<div
    id="screen-welcome"
    class="booth-screen welcome-screen @if(!$hasBackground && !$fallbackBgColor) welcome-screen--no-bg @endif"
    data-state="IDLE"
    @if ($screenStyle) style="<?php echo e($screenStyle); ?>" @endif
>
    {{-- Background Layer --}}
    @if ($hasBackground)
        @php
            $bgPath = $backgroundComponent->content['path'];
            $bgUrl = (strpos($bgPath, 'http') === 0) 
                ? $bgPath 
                : Storage::disk('public')->url($bgPath);
        @endphp
        <img
            src="{{ $bgUrl }}"
            alt="Background"
            class="welcome-background"
        />
    @endif

    {{-- Action Buttons (Top Right) --}}
    <div class="welcome-actions">
        <button
            type="button"
            id="btn-lock-fullscreen"
            class="booth-icon-btn"
            title="Toggle Fullscreen"
            aria-label="Toggle fullscreen"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
        </button>
        <button
            type="button"
            id="btn-camera-settings"
            class="booth-icon-btn"
            title="Camera Settings"
            aria-label="Camera settings"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>
    </div>

    {{-- Content Layer (absolute positioning by x,y) --}}
    <div class="welcome-content welcome-content--positioned">
        {{-- Render Components (excluding background; button handled separately) --}}
        @foreach ($welcomeComponents->where('type', '!=', 'background') as $component)
            @php
                $rawX = $component->content['x'] ?? 960;
                $rawY = $component->content['y'] ?? 540;
                $cx = (int) $rawX;
                $cy = (int) $rawY;
                $leftPct = $cx <= 100 ? $cx : ($cx / 1920) * 100;
                $topPct = $cy <= 100 ? $cy : ($cy / 1080) * 100;
                $cw = $component->content['layoutWidth'] ?? $component->content['width'] ?? 'auto';
                $ch = $component->content['layoutHeight'] ?? $component->content['height'] ?? 'auto';
                $br = (int) ($component->content['borderRadius'] ?? 12);
                $fs = $component->content['fontSize'] ?? 'medium';
                $posStyle = 'left:' . $leftPct . '%;top:' . $topPct . '%;transform:translate(-50%,-50%);';
                $posStyle .= 'width:' . (is_numeric($cw) ? $cw . 'px' : $cw) . ';';
                $posStyle .= 'height:' . (is_numeric($ch) ? $ch . 'px' : $ch) . ';';
                $posStyle .= 'border-radius:' . $br . 'px;';
                $btnFs = match ($fs) {
                    'small' => '1rem',
                    'large' => '1.5rem',
                    default => '1.25rem',
                };
                $buttonStyle = 'border-radius:' . $br . 'px;font-size:' . $btnFs . ';width:100%;height:100%;';
                if (!empty($component->content['backgroundColor'])) {
                    $buttonStyle .= 'background-color:' . e($component->content['backgroundColor']) . ';';
                }
                if (!empty($component->content['buttonTextColor'])) {
                    $buttonStyle .= 'color:' . e($component->content['buttonTextColor']) . ';';
                }
            @endphp
            @if ($component->type === 'button')
                <div class="welcome-component welcome-component--button" style="<?php echo e($posStyle); ?>">
                    <button
                        type="button"
                        id="btn-start"
                        class="welcome-start-btn animate-bounce"
                        style="<?php echo e($buttonStyle); ?>"
                    >
                        {{ $component->content['text'] ?? 'Tap to Start' }}
                    </button>
                </div>
            @else
                @php
                    $fontSize = match ($fs) {
                        'small' => '1.25rem',
                        'large' => '3rem',
                        default => '2rem',
                    };
                    $alignment = $component->content['alignment'] ?? 'center';
                    $textColorVal = $component->content['textColor'] ?? '#ffffff';
                    $textStyle = 'font-size:' . $fontSize . ';color:' . e($textColorVal) . ';';
                @endphp
                <div class="welcome-component" style="<?php echo e($posStyle); ?>">
                    @if ($component->type === 'text')
                        <p class="welcome-text welcome-text--{{ $alignment }}" style="<?php echo e($textStyle); ?>">
                            {{ $component->content['text'] ?? '' }}
                        </p>
                    @elseif ($component->type === 'image' && isset($component->content['path']))
                        @php
                            $imgWidthPx = ($component->content['width'] ?? 'auto') === 'custom'
                                ? (int) ($component->content['customWidth'] ?? 200)
                                : null;
                            $imgPath = $component->content['path'];
                            $imgUrl = (strpos($imgPath, 'http') === 0) 
                                ? $imgPath 
                                : Storage::disk('public')->url($imgPath);
                        @endphp
                        <img
                            src="{{ $imgUrl }}"
                            alt="Welcome Image"
                            class="welcome-image"
                            @if ($imgWidthPx) width="{{ $imgWidthPx }}" @endif
                        />
                    @endif
                </div>
            @endif
        @endforeach

        {{-- Default Start Button if no button component --}}
        @if (!$welcomeComponents->contains('type', 'button'))
            <div class="welcome-component welcome-component--button welcome-component--default-btn">
                <button type="button" id="btn-start" class="welcome-start-btn animate-bounce">
                    Tap to Start
                </button>
            </div>
        @endif
    </div>
</div>
