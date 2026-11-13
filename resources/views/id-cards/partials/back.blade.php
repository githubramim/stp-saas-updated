{{-- Back side of a printable ID card. Expects $person, $design, $contact, $institute. --}}
<div class="idc idc-reverse" style="background: {{ $design['background'] }}; color: {{ $design['text'] }};">
    <div class="idc-band" style="background: linear-gradient(135deg, {{ $design['accent'] }}, {{ $design['accent2'] }});"></div>

    <div class="idc-head">
        <div class="idc-logo idc-logo-fallback">{{ mb_strtoupper(mb_substr($institute->name, 0, 2)) }}</div>
        <div class="idc-head-text">
            <div class="idc-institute">{{ $institute->name }}</div>
            <div class="idc-title">{{ $design['title'] }}</div>
        </div>
    </div>

    <div class="idc-back-body">
        <div class="idc-back-main">
            @if($design['instructions'])
                <div class="idc-instructions">{{ $design['instructions'] }}</div>
            @endif

            <div class="idc-contact">
                @if($design['show_address'] && $contact['address'])
                    <div><span>Address:</span> {{ $contact['address'] }}</div>
                @endif
                @if($design['show_phone'] && $contact['phone'])
                    <div><span>Phone:</span> {{ $contact['phone'] }}</div>
                @endif
                @if($design['show_email'] && $contact['email'])
                    <div><span>Email:</span> {{ $contact['email'] }}</div>
                @endif
                @if($design['show_website'] && $contact['website'])
                    <div><span>Web:</span> {{ $contact['website'] }}</div>
                @endif
            </div>
        </div>

        @if($design['show_qr'])
            <div class="idc-qr idc-qr-static">
                <img src="{{ $person['qr_image'] }}" alt="Verification QR code">
                <span>Scan to verify</span>
            </div>
        @endif
    </div>

    <div class="idc-foot">
        <span>{{ $person['id_no'] }} &middot; {{ $person['name'] }}</span>
        <span>Powered by TrainingPro</span>
    </div>
</div>
