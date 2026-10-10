{{--
    Internship attestation PDF (rendered by dompdf).
    dompdf has no flexbox/grid and cannot load the Vite bundle, so this
    template uses tables and inline CSS only. DejaVu covers French accents.
--}}
@use('Carbon\Carbon')
@php
    $internship = $record['internship'];
    $holder = $record['holder'];
    $tracks = $record['tracks'] ?? [];
    $primary = $tracks[0] ?? null;
    $trackName = $primary['name'] ?? null;

    $start = Carbon::parse($internship['start_date']);
    $end = Carbon::parse($internship['end_date']);
    $issued = Carbon::parse($award['issued_at'] ?? now());
    $fr = fn (Carbon $d) => $d->copy()->locale('fr')->translatedFormat('j F Y');
    $en = fn (Carbon $d) => $d->format('j F Y');

    $reference = $attestation->reference ?? 'DRAFT';
    $weeks = $internship['weeks'];

    $affiliationEn = match ($internship['type']) {
        'academic' => ', ' . ($internship['academic_level'] ? $internship['academic_level'] . ' student' : 'student') . ' at ' . $internship['institution'],
        'corporate' => ', of ' . $internship['institution'],
        default => '',
    };
    $affiliationFr = match ($internship['type']) {
        'academic' => ', étudiant(e)' . ($internship['academic_level'] ? ' (' . $internship['academic_level'] . ')' : '') . ' à ' . $internship['institution'],
        'corporate' => ', de ' . $internship['institution'],
        default => '',
    };

    $projects = $record['projects'] ?? [];
    $interviews = $record['interviews'] ?? ['count' => 0, 'items' => []];
    $attendance = $record['attendance'] ?? null;
    $activitiesDone = collect($tracks)->sum(fn ($t) => $t['curriculum']['activities_completed']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reference }}</title>
    <style>
        @page { margin: 14mm 16mm 18mm 16mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1f2937; line-height: 1.45; }
        table { border-collapse: collapse; width: 100%; }
        .serif { font-family: 'DejaVu Serif', serif; }
        .muted { color: #6b7280; }
        .small { font-size: 8pt; }
        .tiny { font-size: 7pt; }
        .center { text-align: center; }
        .right { text-align: right; }
        .purple { color: #5b21b6; }
        .rule { border-top: 2px solid #5b21b6; height: 0; margin: 6pt 0 10pt 0; }

        .footer { position: fixed; bottom: -11mm; left: 0; right: 0; font-size: 7pt; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 3pt; }
        .pagenum:before { content: counter(page); }

        .watermark { position: fixed; top: 105mm; left: 5mm; width: 170mm; text-align: center; font-size: 90pt; font-weight: bold; color: #dc2626; opacity: 0.12; transform: rotate(-30deg); }

        .title { font-size: 20pt; font-weight: bold; letter-spacing: 2pt; color: #111827; }
        .subtitle { font-size: 13pt; letter-spacing: 1.5pt; color: #5b21b6; }
        .name { font-size: 19pt; font-weight: bold; color: #111827; }
        .lang { width: 50%; vertical-align: top; padding: 0 8pt; text-align: justify; }
        .lang-tag { font-size: 7pt; font-weight: bold; letter-spacing: 1pt; color: #5b21b6; }
        .mention { border: 1.5px solid #5b21b6; padding: 7pt 10pt; }
        .mention-value { font-size: 14pt; font-weight: bold; color: #5b21b6; }

        .page-break { page-break-before: always; }
        h2 { font-size: 11pt; color: #5b21b6; margin: 14pt 0 5pt 0; border-bottom: 1px solid #ddd6fe; padding-bottom: 2pt; }
        h3 { font-size: 9.5pt; margin: 9pt 0 3pt 0; color: #111827; }
        .data th { background: #f5f3ff; text-align: left; font-size: 8pt; padding: 4pt 5pt; color: #4b5563; border-bottom: 1px solid #ddd6fe; }
        .data td { font-size: 8.5pt; padding: 3.5pt 5pt; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .tile { border: 1px solid #e5e7eb; padding: 6pt 4pt; text-align: center; }
        .tile-value { font-size: 14pt; font-weight: bold; color: #111827; }
        .kv td { font-size: 9pt; padding: 2pt 0; vertical-align: top; }
        .kv td.k { color: #6b7280; width: 34%; }
    </style>
</head>
<body>

@if($draft)
    <div class="watermark">DRAFT</div>
@endif

<div class="footer">
    <table>
        <tr>
            <td style="width: 28%;">{{ $reference }} &middot; I-NNOVA CMR</td>
            <td class="right">Verify / Vérifier : {{ $attestation->verify_url }} &middot; Page <span class="pagenum"></span></td>
        </tr>
    </table>
</div>

{{-- ═══════════════ PAGE 1 — FORMAL ATTESTATION ═══════════════ --}}
<table>
    <tr>
        <td style="width: 62%; vertical-align: top;">
            @if($images['logo'])
                <img src="{{ $images['logo'] }}" style="height: 44pt;" alt="">
            @else
                <div style="font-size: 18pt; font-weight: bold; color: #111827;">I-NNOVA CMR</div>
            @endif
            <div class="small muted" style="letter-spacing: 1pt;">INNOVA KICKSTARTER &middot; CAREER CAPITAL PLATFORM</div>
            <div class="small muted">{{ $org['address'] }}</div>
            <div class="small muted">{{ $org['email'] }}@if($org['email'] && $org['website']) &middot; @endif{{ $org['website'] }}</div>
        </td>
        <td class="right small" style="vertical-align: top;">
            <div class="muted">Reference / Référence</div>
            <div style="font-size: 11pt; font-weight: bold;">{{ $reference }}</div>
            <div class="muted" style="margin-top: 4pt;">Date</div>
            <div>{{ $en($issued) }}</div>
        </td>
    </tr>
</table>
<div class="rule"></div>

<div class="center" style="margin-top: 10pt;">
    <div class="title serif">INTERNSHIP ATTESTATION</div>
    <div class="subtitle serif">ATTESTATION DE STAGE</div>
</div>

<div class="center" style="margin-top: 14pt;">
    <div class="small muted">This is to certify that / Nous attestons que</div>
    <div class="name serif" style="margin-top: 5pt;">{{ $holder['name'] }}</div>
</div>

<table style="margin-top: 14pt;">
    <tr>
        <td class="lang" style="border-right: 1px solid #e5e7eb;">
            <div class="lang-tag">ENGLISH</div>
            <p style="margin: 4pt 0 0 0;">
                I-NNOVA CMR, through its INNOVA Kickstarter programme, hereby certifies that
                <strong>{{ $holder['name'] }}</strong>{{ $affiliationEn }},
                completed an internship of <strong>{{ $weeks }} {{ $weeks === 1 ? 'week' : 'weeks' }}</strong>
                @if($trackName) in <strong>{{ $trackName }}</strong> @endif
                from <strong>{{ $en($start) }}</strong> to <strong>{{ $en($end) }}</strong>.
            </p>
            <p style="margin: 6pt 0 0 0;">
                The work carried out during this period is detailed in the Internship Record annexed to this attestation.
                This attestation is issued to serve where and when necessary.
            </p>
        </td>
        <td class="lang">
            <div class="lang-tag">FRANÇAIS</div>
            <p style="margin: 4pt 0 0 0;">
                I-NNOVA CMR, à travers son programme INNOVA Kickstarter, atteste que
                <strong>{{ $holder['name'] }}</strong>{{ $affiliationFr }},
                a effectué un stage de <strong>{{ $weeks }} {{ $weeks === 1 ? 'semaine' : 'semaines' }}</strong>
                @if($trackName) en <strong>{{ $trackName }}</strong> @endif
                du <strong>{{ $fr($start) }}</strong> au <strong>{{ $fr($end) }}</strong>.
            </p>
            <p style="margin: 6pt 0 0 0;">
                Les travaux réalisés durant cette période sont détaillés dans le Relevé de stage annexé à la présente.
                En foi de quoi la présente attestation lui est délivrée pour servir et valoir ce que de droit.
            </p>
        </td>
    </tr>
</table>

@if(!empty($award['mention_en']))
    <table style="margin-top: 14pt;">
        <tr>
            <td style="width: 25%;"></td>
            <td class="mention center">
                <div class="tiny muted" style="letter-spacing: 1pt;">MENTION</div>
                <div class="mention-value serif">
                    {{ $award['mention_en'] }}@if($award['mention_fr'] !== $award['mention_en']) / {{ $award['mention_fr'] }}@endif
                </div>
            </td>
            <td style="width: 25%;"></td>
        </tr>
    </table>
@endif

<table style="margin-top: 18pt; page-break-inside: avoid;">
    <tr>
        <td style="width: 46%; vertical-align: bottom;">
            <table>
                <tr>
                    <td style="width: 82pt; vertical-align: top;">
                        <img src="{{ $qr }}" style="width: 78pt; height: 78pt;" alt="QR">
                    </td>
                    <td class="small" style="vertical-align: top; padding-left: 6pt;">
                        <strong>Scan to verify</strong><br>
                        <span class="muted">Scannez pour vérifier</span><br>
                        @if($attestation->verification_code)
                            <span class="muted">Code :</span> <strong>{{ $attestation->verification_code }}</strong><br>
                        @endif
                        @if($attestation->fingerprint)
                            <span class="muted tiny">Fingerprint / Empreinte :</span><br>
                            <span class="tiny">{{ $attestation->fingerprint }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td style="vertical-align: bottom;" class="center">
            <div class="small">
                Done at {{ $award['place'] ?? '' }}, on {{ $en($issued) }}<br>
                <span class="muted">Fait à {{ $award['place'] ?? '' }}, le {{ $fr($issued) }}</span>
            </div>
            <div style="height: 54pt; margin-top: 3pt;">
                @if($images['signature'])
                    <img src="{{ $images['signature'] }}" style="height: 50pt;" alt="">
                @endif
                @if($images['stamp'])
                    <img src="{{ $images['stamp'] }}" style="height: 54pt; margin-left: 8pt;" alt="">
                @endif
            </div>
            <div style="border-top: 1px solid #9ca3af; width: 92%; margin: 0 auto; padding-top: 3pt;">
                <strong>{{ $award['signatory_name'] ?? '' }}</strong><br>
                <span class="small muted">{{ $award['signatory_title'] ?? '' }}@if(!empty($award['signatory_title_fr'])) / {{ $award['signatory_title_fr'] }}@endif</span>
            </div>
        </td>
    </tr>
</table>

{{-- ═══════════════ ANNEX — INTERNSHIP RECORD ═══════════════ --}}
<div class="page-break"></div>

<table>
    <tr>
        <td>
            <div style="font-size: 14pt; font-weight: bold;" class="serif">INTERNSHIP RECORD <span class="purple">/ RELEVÉ DE STAGE</span></div>
            <div class="small muted">Annex to attestation {{ $reference }} &middot; Annexe à l'attestation {{ $reference }}</div>
        </td>
    </tr>
</table>
<div class="rule"></div>

<table class="kv">
    <tr><td class="k">Name / Nom</td><td><strong>{{ $holder['name'] }}</strong></td></tr>
    <tr><td class="k">Status / Statut</td><td>{{ $holder['fellow_type_label'] ?? '—' }}</td></tr>
    <tr><td class="k">{{ $internship['type'] === 'independent' ? 'Project / Projet' : 'Institution / Établissement' }}</td><td>{{ $internship['institution'] }}@if($internship['department']) &middot; {{ $internship['department'] }}@endif</td></tr>
    @if($internship['academic_level'])
        <tr><td class="k">Level / Niveau</td><td>{{ $internship['academic_level'] }}</td></tr>
    @endif
    @if($internship['student_id'])
        <tr><td class="k">Student ID / Matricule</td><td>{{ $internship['student_id'] }}</td></tr>
    @endif
    <tr><td class="k">{{ $internship['type'] === 'independent' ? 'Mentor / Mentor' : 'Supervisor / Encadreur' }}</td><td>{{ $internship['supervisor_name'] }}</td></tr>
    <tr><td class="k">Period / Période</td><td>{{ $en($start) }} – {{ $en($end) }} ({{ $internship['days'] }} days / jours)</td></tr>
    @if($trackName)
        <tr><td class="k">Track / Filière</td><td>{{ collect($tracks)->pluck('name')->join(', ') }}</td></tr>
    @endif
</table>

{{-- Summary tiles --}}
<table style="margin-top: 10pt;">
    <tr>
        <td class="tile" style="width: 20%;">
            <div class="tile-value">{{ $activitiesDone }}</div>
            <div class="tiny muted">Curriculum activities<br>Activités du parcours</div>
        </td>
        <td class="tile" style="width: 20%;">
            <div class="tile-value">{{ count($projects) }}</div>
            <div class="tiny muted">Projects &amp; contributions<br>Projets et contributions</div>
        </td>
        <td class="tile" style="width: 20%;">
            <div class="tile-value">{{ $interviews['count'] }}</div>
            <div class="tiny muted">Mock interviews<br>Entretiens simulés</div>
        </td>
        <td class="tile" style="width: 20%;">
            <div class="tile-value">{{ isset($attendance['rate']) ? $attendance['rate'] . '%' : '—' }}</div>
            <div class="tiny muted">Attendance<br>Assiduité</div>
        </td>
        <td class="tile" style="width: 20%;">
            <div class="tile-value">{{ $primary ? $primary['career_capital']['score'] . '%' : '—' }}</div>
            <div class="tiny muted">Career Capital{{ $primary ? ' · ' . $primary['career_capital']['tier_label'] : '' }}<br>Capital carrière</div>
        </td>
    </tr>
</table>

{{-- Curriculum per track --}}
@foreach($tracks as $track)
    @if(!empty($track['curriculum']['milestones']))
        <h2>{{ $track['name'] }} — Curriculum / Parcours</h2>
        <div class="small muted">
            {{ $track['curriculum']['required_completed'] }} of {{ $track['curriculum']['required_total'] }} required activities completed
            @if($track['curriculum']['average_score'] !== null) &middot; average score {{ $track['curriculum']['average_score'] }}% @endif
            &middot; {{ $track['curriculum']['points'] }} points
        </div>
        @foreach($track['curriculum']['milestones'] as $milestone)
            <h3>{{ $milestone['title'] }} <span class="muted small">({{ $milestone['completed'] }}/{{ $milestone['total'] }})</span></h3>
            <table class="data">
                <tr>
                    <th>Activity / Activité</th>
                    <th style="width: 22%;">Type</th>
                    <th style="width: 15%;">Completed / Terminé</th>
                    <th style="width: 10%;" class="right">Score</th>
                </tr>
                @foreach($milestone['activities'] as $activity)
                    <tr>
                        <td>{{ $activity['title'] }}</td>
                        <td>{{ $activity['type'] }}</td>
                        <td>{{ $activity['completed_at'] ? Carbon::parse($activity['completed_at'])->format('d M Y') : '—' }}</td>
                        <td class="right">{{ $activity['score'] }}%</td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endif
@endforeach

@if($projects)
    <h2>Projects &amp; contributions / Projets et contributions</h2>
    <table class="data">
        <tr>
            <th>Title / Titre</th>
            <th style="width: 18%;">Type</th>
            <th style="width: 30%;">Technologies</th>
            <th style="width: 12%;">Date</th>
        </tr>
        @foreach($projects as $project)
            <tr>
                <td>
                    {{ $project['title'] }}
                    @if($project['url'])<br><span class="tiny muted">{{ $project['url'] }}</span>@endif
                </td>
                <td>{{ $project['type'] }}</td>
                <td>{{ implode(', ', $project['tech_stack']) ?: '—' }}</td>
                <td>{{ $project['date'] ? Carbon::parse($project['date'])->format('d M Y') : '—' }}</td>
            </tr>
        @endforeach
    </table>
@endif

@if($interviews['count'] > 0)
    <h2>Mock interviews / Entretiens simulés</h2>
    <div class="small muted">
        {{ $interviews['count'] }} completed
        @if($interviews['average_score'] !== null) &middot; average score {{ $interviews['average_score'] }}% @endif
    </div>
    <table class="data" style="margin-top: 3pt;">
        <tr>
            <th>Interview / Entretien</th>
            <th style="width: 24%;">Type</th>
            <th style="width: 15%;">Date</th>
            <th style="width: 10%;" class="right">Score</th>
        </tr>
        @foreach($interviews['items'] as $interview)
            <tr>
                <td>{{ $interview['title'] }}</td>
                <td>{{ $interview['type'] ?? '—' }}{{ $interview['mode'] ? ' (' . strtoupper($interview['mode']) . ')' : '' }}</td>
                <td>{{ $interview['date'] ? Carbon::parse($interview['date'])->format('d M Y') : '—' }}</td>
                <td class="right">{{ $interview['score'] !== null ? $interview['score'] . '%' : '—' }}</td>
            </tr>
        @endforeach
    </table>
@endif

@if($attendance)
    <h2>Attendance / Assiduité</h2>
    <table class="data">
        <tr>
            <th>Sessions held / Séances</th>
            <th>Present / Présent</th>
            <th>Late / En retard</th>
            <th>Absent</th>
            <th>On leave / Permission</th>
            <th>Hours on site / Heures</th>
            <th class="right">Rate / Taux</th>
        </tr>
        <tr>
            <td>{{ $attendance['sessions'] }}</td>
            <td>{{ $attendance['present'] }}</td>
            <td>{{ $attendance['late'] }}</td>
            <td>{{ $attendance['absent'] }}</td>
            <td>{{ $attendance['on_leave'] }}</td>
            <td>{{ $attendance['hours'] }}</td>
            <td class="right">{{ $attendance['rate'] !== null ? $attendance['rate'] . '%' : '—' }}</td>
        </tr>
    </table>
@endif

@if($primary)
    <h2>Career Capital — {{ $primary['name'] }}</h2>
    <div class="small muted">
        Readiness score at date of issue / Score de préparation à la date de délivrance :
        <strong style="color: #111827;">{{ $primary['career_capital']['score'] }}% &middot; {{ $primary['career_capital']['tier_label'] }}</strong>
    </div>
    <table class="data" style="margin-top: 3pt;">
        <tr>
            @foreach($primary['career_capital']['categories'] as $label => $score)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($primary['career_capital']['categories'] as $score)
                <td>{{ $score }}%</td>
            @endforeach
        </tr>
    </table>
@endif

@if(!empty($record['technologies']))
    <h2>Technologies used / Technologies utilisées</h2>
    <div style="font-size: 9pt;">{{ implode(' · ', $record['technologies']) }}</div>
@endif

@if(!empty($record['badges']) || !empty($record['highlights']['pod_lead']) || ($record['highlights']['longest_streak_weeks'] ?? 0) > 1)
    <h2>Distinctions</h2>
    <div style="font-size: 9pt;">
        @if(!empty($record['highlights']['pod_lead']))
            Mentorship pod lead / Chef de groupe de mentorat{{ $record['highlights']['pod_name'] ? ' (' . $record['highlights']['pod_name'] . ')' : '' }}<br>
        @endif
        @if(($record['highlights']['longest_streak_weeks'] ?? 0) > 1)
            {{ $record['highlights']['longest_streak_weeks'] }} consecutive weeks of completed objectives / semaines consécutives d'objectifs atteints<br>
        @endif
        @if(!empty($record['badges']))
            Badges: {{ collect($record['badges'])->pluck('name')->join(', ') }}
        @endif
    </div>
@endif

@if(!empty($award['remarks_en']) || !empty($award['remarks_fr']))
    <h2>Appreciation / Appréciation</h2>
    @if(!empty($award['remarks_en']))
        <p style="margin: 0 0 4pt 0; font-size: 9pt;">{{ $award['remarks_en'] }}</p>
    @endif
    @if(!empty($award['remarks_fr']))
        <p style="margin: 0; font-size: 9pt;" class="muted">{{ $award['remarks_fr'] }}</p>
    @endif
@endif

<p class="tiny muted" style="margin-top: 14pt;">
    Every item in this record was reviewed and approved on the IKS Career Capital Platform before being counted.
    Chaque élément de ce relevé a été examiné et validé sur la plateforme IKS Career Capital avant d'être comptabilisé.
</p>

</body>
</html>
