<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data Science: the coding and analysis exercises ("research" activities in
 * milestones 2 onwards: Python, statistics, pandas, cleaning, visualisation,
 * EDA) counted toward the interview part of Career Capital. They are technical
 * work, so they now count as technical. Milestone 1's orientation readings are
 * unchanged.
 *
 * The curriculum mock interviews also get a real interview type: the Python
 * check was "behavioral", and later ones said "mock_interview", which is not a
 * type, so all of them ran as generic behavioral interviews.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->exercises()->where('a.career_capital_category', 'interview')
            ->update(['a.career_capital_category' => 'technical', 'a.updated_at' => now()]);

        foreach ($this->mockInterviews()->get(['a.id', 'a.interview_config']) as $row) {
            $config = json_decode($row->interview_config ?? '[]', true) ?: [];
            if (($config['type'] ?? null) === 'technical_coding') {
                continue;
            }
            $config['type'] = 'technical_coding';
            DB::table('track_curriculum_activities')->where('id', $row->id)
                ->update(['interview_config' => json_encode($config), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Before this migration none of these exercises were "technical".
        $this->exercises()->where('a.career_capital_category', 'technical')
            ->update(['a.career_capital_category' => 'interview', 'a.updated_at' => now()]);
    }

    protected function exercises()
    {
        return DB::table('track_curriculum_activities as a')
            ->join('track_milestones as m', 'm.id', '=', 'a.milestone_id')
            ->join('tracks as t', 't.id', '=', 'a.track_id')
            ->where('t.slug', 'data-science')
            ->where('a.type', 'research')
            ->where('m.sequence_order', '>', 1)
            ->whereNull('a.deleted_at');
    }

    /** Mock interviews after the orientation milestone (its check-in stays behavioral). */
    protected function mockInterviews()
    {
        return DB::table('track_curriculum_activities as a')
            ->join('track_milestones as m', 'm.id', '=', 'a.milestone_id')
            ->join('tracks as t', 't.id', '=', 'a.track_id')
            ->where('t.slug', 'data-science')
            ->where('a.type', 'mock_interview')
            ->where('m.sequence_order', '>', 1)
            ->whereNull('a.deleted_at');
    }
};
