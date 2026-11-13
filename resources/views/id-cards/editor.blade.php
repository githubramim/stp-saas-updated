<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="flex items-center gap-2 text-lg font-semibold leading-tight text-gray-800 sm:text-xl">
                    <i class="bi bi-sliders2 text-teal-500"></i>
                    <span class="truncate">{{ __('ID Card Design Editor') }}</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $type === 'student' ? __('Student card template') : __('Employee card template') }}
                    &middot; {{ $institute->name }}
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('id-cards.index', ['type' => $type]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-800">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('Cards') }}
                </a>
                <button type="submit" form="designForm" class="btn-solid-indigo">
                    <i class="bi bi-check2-circle"></i>
                    {{ __('Save design') }}
                </button>
            </div>
        </div>
    </x-slot>

    @push('styles')
        @include('id-cards.partials.card-style')
        <style>
            [x-cloak] { display: none !important; }
            .idc-preview .idc { box-shadow: 0 18px 30px -18px rgba(15, 23, 42, .45); }
        </style>
    @endpush

    @php
        $inputClass = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30';
        $labelClass = 'mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500';
        $toggleClass = 'flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50/50';
        $checkClass = 'h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500';
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 inline-flex rounded-xl border border-slate-200 bg-slate-100 p-1">
                <a href="{{ route('id-cards.editor', ['type' => 'student']) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $type === 'student' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    <i class="bi bi-mortarboard"></i> {{ __('Student card') }}
                </a>
                <a href="{{ route('id-cards.editor', ['type' => 'employee']) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $type === 'employee' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    <i class="bi bi-person-badge"></i> {{ __('Employee card') }}
                </a>
            </div>

            <div class="grid items-start gap-5 lg:grid-cols-3"
                 x-data="{
                    design: {{ Js::from($design) }},
                    defaults: {{ Js::from(\App\Models\IdCardTemplate::defaults($type)) }},
                    sample: {{ Js::from($sample) }},
                    institute: {{ Js::from([
                        'name' => $institute->name,
                        'initials' => mb_strtoupper(mb_substr($institute->name, 0, 2)),
                        'logo' => $institute->logo ? asset('storage/' . $institute->logo) : null,
                        'address' => $institute->address,
                        'mobile' => $institute->mobile,
                        'email' => $institute->email,
                    ]) }}
                 }">

                {{-- ===================== FORM ===================== --}}
                <form id="designForm" method="POST" action="{{ route('id-cards.editor.update', ['type' => $type]) }}"
                      class="order-2 space-y-5 lg:order-1 lg:col-span-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="{{ $type }}">

                    {{-- FRONT --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3.5 sm:px-5">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i class="bi bi-card-heading"></i></span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-700">{{ __('Front side') }}</h3>
                                    <p class="text-xs text-slate-400">{{ __('Identity, photo and QR verification code.') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">CR80 85.6 × 53.98 mm</span>
                        </div>

                        <div class="space-y-4 p-4 sm:p-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="front_title" class="{{ $labelClass }}">{{ __('Card title') }}</label>
                                    <input id="front_title" type="text" name="front[title]" x-model="design.front.title" maxlength="80" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label for="front_subtitle" class="{{ $labelClass }}">{{ __('Subtitle') }}</label>
                                    <input id="front_subtitle" type="text" name="front[subtitle]" x-model="design.front.subtitle" maxlength="120" class="{{ $inputClass }}">
                                </div>
                            </div>

                            <div>
                                <label for="front_footer" class="{{ $labelClass }}">{{ __('Footer note') }}</label>
                                <input id="front_footer" type="text" name="front[footer]" x-model="design.front.footer" maxlength="180" class="{{ $inputClass }}">
                            </div>

                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <label for="front_accent" class="{{ $labelClass }}">{{ __('Accent') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="front_accent" type="color" name="front[accent]" x-model="design.front.accent"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.front.accent"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="front_accent2" class="{{ $labelClass }}">{{ __('Accent 2') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="front_accent2" type="color" name="front[accent2]" x-model="design.front.accent2"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.front.accent2"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="front_background" class="{{ $labelClass }}">{{ __('Card') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="front_background" type="color" name="front[background]" x-model="design.front.background"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.front.background"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="front_text" class="{{ $labelClass }}">{{ __('Ink') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="front_text" type="color" name="front[text]" x-model="design.front.text"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.front.text"></code>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="{{ $labelClass }}">{{ __('Show on card') }}</p>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_logo]" value="1" x-model="design.front.show_logo" class="{{ $checkClass }}"> {{ __('Institute logo') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_photo]" value="1" x-model="design.front.show_photo" class="{{ $checkClass }}"> {{ __('Photo') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_qr]" value="1" x-model="design.front.show_qr" class="{{ $checkClass }}"> {{ __('QR code') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_id_no]" value="1" x-model="design.front.show_id_no" class="{{ $checkClass }}"> {{ __('ID number') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_line1]" value="1" x-model="design.front.show_line1" class="{{ $checkClass }}"> {{ $type === 'student' ? __('Course') : __('Designation') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_line2]" value="1" x-model="design.front.show_line2" class="{{ $checkClass }}"> {{ $type === 'student' ? __('Batch') : __('Institute') }}</label>
                                    @if($type === 'student')
                                        <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_blood]" value="1" x-model="design.front.show_blood" class="{{ $checkClass }}"> {{ __('Blood group') }}</label>
                                    @endif
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_phone]" value="1" x-model="design.front.show_phone" class="{{ $checkClass }}"> {{ __('Contact') }}</label>
                                    @if($type === 'student')
                                        <label class="{{ $toggleClass }}"><input type="checkbox" name="front[show_valid]" value="1" x-model="design.front.show_valid" class="{{ $checkClass }}"> {{ __('Valid until') }}</label>
                                    @endif
                                </div>
                                @if($type === 'employee')
                                    <input type="hidden" name="front[show_blood]" value="0">
                                    <input type="hidden" name="front[show_valid]" value="0">
                                @endif
                            </div>
                        </div>
                    </section>

                    {{-- BACK --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3.5 sm:px-5">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600"><i class="bi bi-card-text"></i></span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-700">{{ __('Back side') }}</h3>
                                    <p class="text-xs text-slate-400">{{ __('Return instructions and institute contact details.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 p-4 sm:p-5">
                            <div>
                                <label for="back_title" class="{{ $labelClass }}">{{ __('Card title') }}</label>
                                <input id="back_title" type="text" name="back[title]" x-model="design.back.title" maxlength="80" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="back_instructions" class="{{ $labelClass }}">{{ __('Instructions') }}</label>
                                <textarea id="back_instructions" name="back[instructions]" x-model="design.back.instructions" rows="3" maxlength="600" class="{{ $inputClass }}"></textarea>
                                <p class="mt-1 text-xs text-slate-400">{{ __('Each line is printed as a separate line.') }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="back_address" class="{{ $labelClass }}">{{ __('Address override') }}</label>
                                    <input id="back_address" type="text" name="back[address]" x-model="design.back.address" maxlength="200"
                                           placeholder="{{ $institute->address ?: __('Institute address') }}" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label for="back_phone" class="{{ $labelClass }}">{{ __('Phone override') }}</label>
                                    <input id="back_phone" type="text" name="back[phone]" x-model="design.back.phone" maxlength="80"
                                           placeholder="{{ $institute->mobile ?: __('Institute phone') }}" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label for="back_email" class="{{ $labelClass }}">{{ __('Email override') }}</label>
                                    <input id="back_email" type="text" name="back[email]" x-model="design.back.email" maxlength="120"
                                           placeholder="{{ $institute->email ?: __('Institute email') }}" class="{{ $inputClass }}">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="back_website" class="{{ $labelClass }}">{{ __('Website') }}</label>
                                    <input id="back_website" type="text" name="back[website]" x-model="design.back.website" maxlength="160"
                                           placeholder="https://" class="{{ $inputClass }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <label for="back_accent" class="{{ $labelClass }}">{{ __('Accent') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="back_accent" type="color" name="back[accent]" x-model="design.back.accent"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.back.accent"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="back_accent2" class="{{ $labelClass }}">{{ __('Accent 2') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="back_accent2" type="color" name="back[accent2]" x-model="design.back.accent2"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.back.accent2"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="back_background" class="{{ $labelClass }}">{{ __('Card') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="back_background" type="color" name="back[background]" x-model="design.back.background"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.back.background"></code>
                                    </div>
                                </div>
                                <div>
                                    <label for="back_text" class="{{ $labelClass }}">{{ __('Ink') }}</label>
                                    <div class="flex items-center gap-2">
                                        <input id="back_text" type="color" name="back[text]" x-model="design.back.text"
                                               class="h-9 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                        <code class="text-xs font-semibold text-slate-500" x-text="design.back.text"></code>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="{{ $labelClass }}">{{ __('Show on card') }}</p>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="back[show_qr]" value="1" x-model="design.back.show_qr" class="{{ $checkClass }}"> {{ __('QR code') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="back[show_address]" value="1" x-model="design.back.show_address" class="{{ $checkClass }}"> {{ __('Address') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="back[show_phone]" value="1" x-model="design.back.show_phone" class="{{ $checkClass }}"> {{ __('Phone') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="back[show_email]" value="1" x-model="design.back.show_email" class="{{ $checkClass }}"> {{ __('Email') }}</label>
                                    <label class="{{ $toggleClass }}"><input type="checkbox" name="back[show_website]" value="1" x-model="design.back.show_website" class="{{ $checkClass }}"> {{ __('Website') }}</label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <button type="button"
                                @click="design = JSON.parse(JSON.stringify(defaults))"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            {{ __('Reset to defaults') }}
                        </button>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('id-cards.index', ['type' => $type]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-50">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn-solid-indigo">
                                <i class="bi bi-check2-circle"></i>
                                {{ __('Save design') }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- ===================== LIVE PREVIEW ===================== --}}
                <div class="order-1 lg:order-2" x-cloak>
                    <div class="idc-preview space-y-4 lg:sticky lg:top-24">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="flex items-center gap-1.5 text-sm font-bold text-slate-700">
                                        <i class="bi bi-eye-fill text-indigo-500"></i>
                                        {{ __('Live preview') }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Updates as you edit. Actual CR80 size.') }}</p>
                                </div>
                                @if($sample['id'])
                                    <a href="{{ route('id-cards.print', ['type' => $type, 'ids' => [$sample['id']]]) }}"
                                       target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600">
                                        <i class="bi bi-printer"></i> {{ __('Print') }}
                                    </a>
                                @endif
                            </div>

                            <div class="mt-3 overflow-x-auto pb-1">
                                <div class="inline-flex min-w-full flex-col gap-4">
                                    {{-- FRONT PREVIEW --}}
                                    <div>
                                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Front') }}</p>
                                        <div class="idc" :style="{ background: design.front.background, color: design.front.text }">
                                            <div class="idc-band" :style="{ background: 'linear-gradient(135deg, ' + design.front.accent + ', ' + design.front.accent2 + ')' }"></div>

                                            <div class="idc-head">
                                                <template x-if="design.front.show_logo && institute.logo">
                                                    <img class="idc-logo" :src="institute.logo" alt="">
                                                </template>
                                                <template x-if="design.front.show_logo && ! institute.logo">
                                                    <div class="idc-logo idc-logo-fallback" x-text="institute.initials"></div>
                                                </template>
                                                <div class="idc-head-text">
                                                    <div class="idc-institute" x-text="institute.name"></div>
                                                    <div class="idc-title" x-text="design.front.title"></div>
                                                    <div class="idc-subtitle" x-show="design.front.subtitle" x-text="design.front.subtitle"></div>
                                                </div>
                                            </div>

                                            <div class="idc-body" :class="design.front.show_qr ? 'idc-body-qr' : ''">
                                                <div class="idc-photo" x-show="design.front.show_photo">
                                                    <template x-if="sample.photo">
                                                        <img :src="sample.photo" alt="">
                                                    </template>
                                                    <template x-if="! sample.photo">
                                                        <span x-text="sample.initials"></span>
                                                    </template>
                                                </div>

                                                <div class="idc-info">
                                                    <div class="idc-name" x-text="sample.name"></div>
                                                    <div class="idc-name-bn" x-show="sample.name_bn" x-text="sample.name_bn"></div>

                                                    <div class="idc-row" x-show="design.front.show_id_no">
                                                        <span>{{ $type === 'student' ? __('ID No') : __('Employee ID') }}</span>
                                                        <strong x-text="sample.id_no"></strong>
                                                    </div>
                                                    <div class="idc-row" x-show="design.front.show_line1 && sample.line1">
                                                        <span>{{ $type === 'student' ? __('Course') : __('Designation') }}</span>
                                                        <strong x-text="sample.line1"></strong>
                                                    </div>
                                                    <div class="idc-row" x-show="design.front.show_line2 && sample.line2">
                                                        <span>{{ $type === 'student' ? __('Batch') : __('Institute') }}</span>
                                                        <strong x-text="sample.line2"></strong>
                                                    </div>
                                                    @if($type === 'student')
                                                        <div class="idc-row" x-show="design.front.show_blood && sample.blood">
                                                            <span>{{ __('Blood') }}</span>
                                                            <strong x-text="sample.blood"></strong>
                                                        </div>
                                                    @endif
                                                    <div class="idc-row" x-show="design.front.show_phone && sample.phone">
                                                        <span>{{ __('Contact') }}</span>
                                                        <strong x-text="sample.phone"></strong>
                                                    </div>
                                                    @if($type === 'student')
                                                        <div class="idc-row" x-show="design.front.show_valid && sample.valid">
                                                            <span>{{ __('Valid') }}</span>
                                                            <strong x-text="sample.valid"></strong>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="idc-qr" x-show="design.front.show_qr">
                                                    <img :src="sample.qr_image" alt="">
                                                    <span>{{ __('Scan to verify') }}</span>
                                                </div>
                                            </div>

                                            <div class="idc-foot">
                                                <span x-text="design.front.footer"></span>
                                                <span class="idc-chip"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- BACK PREVIEW --}}
                                    <div>
                                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Back') }}</p>
                                        <div class="idc" :style="{ background: design.back.background, color: design.back.text }">
                                            <div class="idc-band" :style="{ background: 'linear-gradient(135deg, ' + design.back.accent + ', ' + design.back.accent2 + ')' }"></div>

                                            <div class="idc-head">
                                                <div class="idc-logo idc-logo-fallback" x-text="institute.initials"></div>
                                                <div class="idc-head-text">
                                                    <div class="idc-institute" x-text="institute.name"></div>
                                                    <div class="idc-title" x-text="design.back.title"></div>
                                                </div>
                                            </div>

                                            <div class="idc-back-body">
                                                <div class="idc-back-main">
                                                    <div class="idc-instructions" x-show="design.back.instructions" x-text="design.back.instructions"></div>
                                                    <div class="idc-contact">
                                                        <div x-show="design.back.show_address && (design.back.address || institute.address)">
                                                            <span>{{ __('Address') }}:</span>
                                                            <span style="font-weight:400;color:inherit" x-text="design.back.address || institute.address"></span>
                                                        </div>
                                                        <div x-show="design.back.show_phone && (design.back.phone || institute.mobile)">
                                                            <span>{{ __('Phone') }}:</span>
                                                            <span style="font-weight:400;color:inherit" x-text="design.back.phone || institute.mobile"></span>
                                                        </div>
                                                        <div x-show="design.back.show_email && (design.back.email || institute.email)">
                                                            <span>{{ __('Email') }}:</span>
                                                            <span style="font-weight:400;color:inherit" x-text="design.back.email || institute.email"></span>
                                                        </div>
                                                        <div x-show="design.back.show_website && design.back.website">
                                                            <span>{{ __('Web') }}:</span>
                                                            <span style="font-weight:400;color:inherit" x-text="design.back.website"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="idc-qr idc-qr-static" x-show="design.back.show_qr">
                                                    <img :src="sample.qr_image" alt="">
                                                    <span>{{ __('Scan to verify') }}</span>
                                                </div>
                                            </div>

                                            <div class="idc-foot">
                                                <span x-text="sample.id_no + ' · ' + sample.name"></span>
                                                <span>{{ __('Powered by TrainingPro') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4 text-xs text-indigo-700">
                            <p class="flex items-center gap-1.5 font-bold"><i class="bi bi-lightbulb"></i> {{ __('Design tips') }}</p>
                            <ul class="mt-1.5 list-disc space-y-1 pl-4">
                                <li>{{ __('Keep your accent colour dark enough for the white header text.') }}</li>
                                <li>{{ __('The QR code points to a public profile with the card holder details.') }}</li>
                                <li>{{ __('Changes apply to every card printed from now on.') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
