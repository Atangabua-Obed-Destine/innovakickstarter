<?php

use App\Models\User;
use App\Models\TrackCurriculumActivity;
use App\Models\FellowCurriculumProgress;
use App\Services\CareerCapitalCalculator;

$fellow = User::where('email', 'sample@gmail.com')->first();
$trackId = '019c2388-bef8-729a-a920-cc0c5036110c';
$track = \App\Models\Track::find($trackId);

$progresses = FellowCurriculumProgress::where('fellow_id', $fellow->id)
    ->whereHas('curriculumActivity', function ($q) use ($trackId) {
        $q->where('track_id', $trackId);
    })->with('curriculumActivity')->get();

foreach ($progresses as $p) {
    echo "Activity: " . $p->curriculumActivity->title . "\n";
    echo "Status: " . $p->status->value . "\n";
    echo "Points Awarded: " . $p->points_awarded . "\n";
    echo "Score Awarded: " . $p->score_awarded . "\n";
    echo "Type: " . ($p->curriculumActivity->type->value ?? 'null') . "\n";
    echo "----------\n";
}

$fellowTrack = $fellow->fellowTracks()->where('track_id', $trackId)->first();
echo "FellowTrack Score: " . ($fellowTrack ? $fellowTrack->score : 'No FellowTrack') . "\n";

// Let's run calculator manually
$calculator = new CareerCapitalCalculator();
echo "Calculated Technical: " . $calculator->calculateTechnicalScore($fellow, $track) . "\n";
echo "Calculated Interview: " . $calculator->calculateInterviewScore($fellow, $track) . "\n";
echo "Calculated Portfolio: " . $calculator->calculatePortfolioScore($fellow, $track) . "\n";
echo "Calculated Collab: " . $calculator->calculateCollaborationScore($fellow, $track) . "\n";
echo "Calculated Learning: " . $calculator->calculateLearningScore($fellow, $track) . "\n";
echo "Final: " . $calculator->calculateScore($fellow, $track) . "\n";

