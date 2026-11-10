<script>
    (function () {
        function initTamAutoFill(prefix, nameFieldId, lookupUrl) {
            var occupation = document.getElementById(prefix + '_tam_occupation');
            var level = document.getElementById(prefix + '_level');
            var sector = document.getElementById(prefix + '_sector');
            var hours = document.getElementById(prefix + '_hours');
            var hint = document.getElementById(prefix + '_hint');
            var nameField = nameFieldId ? document.getElementById(nameFieldId) : null;

            if (!occupation || !level || !sector || !hours) {
                return;
            }

            function setHint(text, tone) {
                if (!hint) {
                    return;
                }
                hint.textContent = text || '';
                hint.className = 'form-text text-[11px] ' + (tone === 'warn' ? 'text-amber-600' : 'text-emerald-600');
            }

            function apply() {
                var selected = occupation.value;

                if (!selected) {
                    setHint('', 'ok');
                    return;
                }

                var params = new URLSearchParams({ occupation: selected });
                if (level.value) {
                    params.set('level', level.value);
                }

                fetch(lookupUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(function (data) {
                        if (!data) {
                            return;
                        }

                        if (data.sector && Array.prototype.some.call(sector.options, function (option) {
                            return option.value === data.sector;
                        })) {
                            sector.value = data.sector;
                        }

                        if (!level.value && data.levels && data.levels.length === 1) {
                            level.value = String(data.levels[0]);
                        }

                        if (level.value && data.durations && data.durations.length) {
                            hours.value = data.durations[0];

                            if (data.durations.length > 1) {
                                setHint(
                                    'TAM List has multiple nominal hours for this Occupation + Level: '
                                        + data.durations.join(', ') + '. Using ' + data.durations[0]
                                        + ' hours — adjust if needed.',
                                    'warn'
                                );
                            } else {
                                setHint('Nominal hour auto-filled from the TAM List.', 'ok');
                            }
                        } else if (data.levels && data.levels.length > 1) {
                            setHint('Select the NTVQF Level to auto-fill the nominal hour.', 'warn');
                        }

                        if (nameField && !nameField.value.trim() && data.occupation) {
                            nameField.value = data.occupation;
                        }
                    })
                    .catch(function () {});
            }

            occupation.addEventListener('change', apply);
            level.addEventListener('change', apply);
        }

        document.addEventListener('DOMContentLoaded', function () {
            initTamAutoFill(
                @json($prefix),
                @json($nameFieldId ?? null),
                @json(route('courses.tam-lookup'))
            );
        });
    })();
</script>
