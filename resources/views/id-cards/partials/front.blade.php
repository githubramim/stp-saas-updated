{{-- Front side of a printable ID card. Expects $person, $design, $institute, $type. --}}
<div class="idc" style="background: {{ $design['background'] }}; color: {{ $design['text'] }};">
    <div class="idc-band" style="background: linear-gradient(135deg, {{ $design['accent'] }}, {{ $design['accent2'] }});"></div>

    <div class="idc-head">
        @if($design['show_logo'])
            @if($institute->logo)
                <img class="idc-logo" src="{{ asset('storage/' . $institute->logo) }}" alt="{{ $institute->name }}">
            @else
                <div class="idc-logo idc-logo-fallback">{{ mb_strtoupper(mb_substr($institute->name, 0, 2)) }}</div>
            @endif
        @endif
        <div class="idc-head-text">
            <div class="idc-institute">{{ $institute->name }}</div>
            <div class="idc-title">{{ $design['title'] }}</div>
            @if($design['subtitle'])
                <div class="idc-subtitle">{{ $design['subtitle'] }}</div>
            @endif
        </div>
    </div>

    <div class="idc-body {{ $design['show_qr'] ? 'idc-body-qr' : '' }}">
        @if($design['show_photo'])
            <div class="idc-photo">
                @if($person['photo'])
                    <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}">
                @else
                    <span>{{ $person['initials'] }}</span>
                @endif
            </div>
        @endif

        <div class="idc-info">
            <div class="idc-name">{{ $person['name'] }}</div>
            @if($person['name_bn'])
                <div class="idc-name-bn">{{ $person['name_bn'] }}</div>
            @endif

            @if($design['show_id_no'])
                <div class="idc-row">
                    <span>{{ $type === 'student' ? 'ID No' : 'Employee ID' }}</span>
                    <strong>{{ $person['id_no'] }}</strong>
                </div>
            @endif
            @if($design['show_line1'] && $person['line1'])
                <div class="idc-row">
                    <span>{{ $type === 'student' ? 'Course' : 'Designation' }}</span>
                    <strong>{{ $person['line1'] }}</strong>
                </div>
            @endif
            @if($design['show_line2'] && $person['line2'])
                <div class="idc-row">
                    <span>{{ $type === 'student' ? 'Batch' : 'Institute' }}</span>
                    <strong>{{ $person['line2'] }}</strong>
                </div>
            @endif
            @if($design['show_blood'] && $person['blood'])
                <div class="idc-row">
                    <span>Blood Group</span>
                    <strong>{{ $person['blood'] }}</strong>
                </div>
            @endif
            @if($design['show_phone'] && $person['phone'])
                <div class="idc-row">
                    <span>Contact</span>
                    <strong>{{ $person['phone'] }}</strong>
                </div>
            @endif
            @if($design['show_valid'] && $person['valid'])
                <div class="idc-row">
                    <span>Valid Until</span>
                    <strong>{{ $person['valid'] }}</strong>
                </div>
            @endif
        </div>

        @if($design['show_qr'])
            <div class="idc-qr">
                <img src="{{ $person['qr_image'] }}" alt="Verification QR code">
                <span>Scan to verify</span>
            </div>
        @endif
    </div>

    <div class="idc-foot">
        <span>{{ $design['footer'] }}</span>
        <span class="idc-chip"></span>
    </div>
</div>
