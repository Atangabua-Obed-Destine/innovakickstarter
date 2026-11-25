<?php

namespace App\Console\Commands;

use App\Models\FellowTrack;
use App\Services\SkillCertificateService;
use Illuminate\Console\Command;

/** Issue skill certificates fellows have already earned (e.g. after skills are first defined). */
class AwardSkillCertificates extends Command
{
    protected $signature = 'certificates:award {--notify : Also notify fellows}';

    protected $description = 'Issue any skill certificates fellows have earned';

    public function handle(SkillCertificateService $certificates): int
    {
        $issued = 0;
        FellowTrack::with(['fellow', 'track'])->get()->each(function ($ft) use ($certificates, &$issued) {
            if ($ft->fellow && $ft->track) {
                $issued += $certificates->checkAndAward($ft->fellow, $ft->track, (bool) $this->option('notify'))->count();
            }
        });
        $this->info("Issued {$issued} certificate(s).");

        return self::SUCCESS;
    }
}
