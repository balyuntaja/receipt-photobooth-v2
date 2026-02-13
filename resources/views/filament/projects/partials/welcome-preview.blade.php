{{--
    Welcome Screen Preview (scaled 16:9 for Project Settings)
    Expects: $welcomeComponents (Collection), optional $welcomeBackgroundColor (fallback when no bg image)
--}}
@php
    $backgroundComponent = $welcomeComponents->firstWhere('type', 'background');
    $hasBackground = $backgroundComponent && isset($backgroundComponent->content['path']);
    $fallbackColor = $welcomeBackgroundColor ?? null;
    $bgLayerStyle = 'position: absolute; inset: 0;';
    if (!$hasBackground) {
        if ($fallbackColor !== null && $fallbackColor !== '') {
            $bgLayerStyle .= ' background-color: ' . e($fallbackColor) . ';';
        } else {
            $bgLayerStyle .= ' background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
        }
    }
@endphp
<div class="welcome-preview-wrapper" style="max-width: 400px; aspect-ratio: 16/9; border-radius: 0.5rem; overflow: hidden; border: 1px solid rgb(229 231 235); box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);">
    <div class="welcome-preview-inner" style="width: 1920px; height: 1080px; transform: scale(0.20833); transform-origin: 0 0; position: relative;">
        <div style="<?php echo e($bgLayerStyle); ?>">
            @if ($hasBackground)
                <img
                    src="{{ Storage::disk('public')->url($backgroundComponent->content['path']) }}"
                    alt=""
                    style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                />
            @endif
        </div>
        <div style="position: absolute; inset: 0; display: block; padding: 0; pointer-events: none;">
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
                    $posStyle = 'position: absolute; left:' . $leftPct . '%; top:' . $topPct . '%; transform: translate(-50%, -50%); display: flex; align-items: center; justify-content: center; box-sizing: border-box;';
                    $posStyle .= ' width:' . (is_numeric($cw) ? $cw . 'px' : $cw) . '; height:' . (is_numeric($ch) ? $ch . 'px' : $ch) . '; border-radius:' . $br . 'px;';
                @endphp
                @if ($component->type === 'button')
                    @php
                        $btnFs = match ($fs) {
                            'small' => '1rem',
                            'large' => '1.5rem',
                            default => '1.25rem',
                        };
                        $btnBg = $component->content['backgroundColor'] ?? '#ffffff';
                        $btnColor = $component->content['buttonTextColor'] ?? '#111';
                        $btnSpanStyle = 'display: inline-block; padding: 0.5rem 1rem; font-size: ' . $btnFs . '; font-weight: 700; background: ' . e($btnBg) . '; color: ' . e($btnColor) . '; border-radius: ' . $br . 'px; white-space: nowrap;';
                    @endphp
                    <div style="<?php echo e($posStyle); ?>">
                        <span style="<?php echo e($btnSpanStyle); ?>">
                            {{ $component->content['text'] ?? 'Tap to Start' }}
                        </span>
                    </div>
                @elseif ($component->type === 'text')
                    @php
                        $fontSize = match ($fs) {
                            'small' => '1.25rem',
                            'large' => '3rem',
                            default => '2rem',
                        };
                        $alignment = $component->content['alignment'] ?? 'center';
                        $textColorVal = $component->content['textColor'] ?? '#ffffff';
                        $textPStyle = 'margin: 0; font-weight: 600; color: ' . e($textColorVal) . '; text-shadow: 0 2px 4px rgba(0,0,0,0.3); font-size: ' . $fontSize . '; text-align: ' . $alignment . ';';
                    @endphp
                    <div style="<?php echo e($posStyle); ?>">
                        <p style="<?php echo e($textPStyle); ?>">
                            {{ Str::limit($component->content['text'] ?? '', 40) }}
                        </p>
                    </div>
                @elseif ($component->type === 'image' && isset($component->content['path']))
                    <div style="<?php echo e($posStyle); ?>">
                        <img
                            src="{{ Storage::disk('public')->url($component->content['path']) }}"
                            alt=""
                            style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem;"
                        />
                    </div>
                @endif
            @endforeach
            @if (!$welcomeComponents->contains('type', 'button'))
                <div style="position: absolute; left: 50%; bottom: 2rem; transform: translateX(-50%);">
                    <span style="display: inline-block; padding: 0.5rem 1rem; font-size: 1.25rem; font-weight: 700; background: #fff; color: #111; border-radius: 9999px;">
                        Tap to Start
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
