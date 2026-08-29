@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('profile.show') }}" class="back"><i class="bi bi-chevron-left"></i> Profil</a>
    <h1>Pusat Bantuan</h1>
</div>

<div class="p-3">
    <div class="pui-card p-3 mb-4" style="background:var(--grad-primary);color:#fff;border:none;">
        <h4 class="fw-bold mb-2">Ada pertanyaan?</h4>
        <p class="mb-0 small opacity-75">Temukan jawaban cepat untuk kendala Anda di bawah ini.</p>
    </div>

    <div class="stagger">
        @foreach($faqs as $index => $faq)
        <div class="pui-card mb-3 overflow-hidden">
            <div class="p-3 d-flex align-items-center justify-content-between"
                 onclick="toggleFaq({{ $index }})" style="cursor:pointer;">
                <div class="fw-bold pe-3" style="font-size:14px;line-height:1.4;">{{ $faq['q'] }}</div>
                <i id="icon-{{ $index }}" class="bi bi-chevron-down text-muted"></i>
            </div>
            <div id="ans-{{ $index }}" class="px-3 pb-3 small text-muted border-top pt-3" style="display:none;line-height:1.6;">
                {{ $faq['a'] }}
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-5 mb-4 px-4 py-4" style="background:#f8fafc; border-radius:var(--radius-md); border:1px dashed var(--line-strong);">
        <i class="bi bi-person-video3 text-primary mb-2" style="font-size:28px;"></i>
        <p class="small text-muted mb-0">Jika masih butuh bantuan atau memiliki kendala yang tidak tercantum di atas, silakan tanyakan kepada <strong>guru pembimbing</strong> Anda.</p>
    </div>
</div>

<script>
function toggleFaq(i) {
    var ans = document.getElementById('ans-' + i);
    var icon = document.getElementById('icon-' + i);
    if (ans.style.display === 'none') {
        ans.style.display = 'block';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
    } else {
        ans.style.display = 'none';
        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
    }
}
</script>
@endsection
