$fellow = \App\Models\User::where('email', 'sample@gmail.com')->first();
$activities = \App\Models\TrackCurriculumActivity::where('milestone_id', '019c5d7d-b064-71d0-af5c-e3abf5b1b4ea')->orderBy('sequence_order')->get();
foreach($activities as $act) {
    $prog = \App\Models\FellowCurriculumProgress::where('fellow_id', $fellow->id)->where('curriculum_activity_id', $act->id)->first();
    echo $act->id . ' | seq:' . $act->sequence_order . ' | seq?:' . $act->is_sequential . ' | Prog: ' . ($prog ? $prog->status->value ?? $prog->status : 'null') . "\n";
}
