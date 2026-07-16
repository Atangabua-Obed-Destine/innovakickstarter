<?php

namespace App\Services;

use Illuminate\Support\Str;

class MarkdownCurriculumParser
{
    /**
     * Parses the content of a markdown milestone file into structured array data.
     */
    public function parse(string $content): array
    {
        // Fix UTF-8 encoding and replace weird characters like em dashes
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        $content = str_replace(['—', '–', '‘', '’', '“', '”'], ['-', '-', "'", "'", '"', '"'], $content);
        // Also strip out any invalid UTF-8 sequences just in case
        $content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        
        $milestone = [
            'title' => '',
            'theme' => '',
            'activities' => [],
        ];
        
        $currentActivity = null;
        $inRubric = false;
        $inPrompt = false;
        
        foreach ($lines as $line) {
            // Milestone Title
            if (preg_match('/^## Milestone \d+:\s*(.+)$/', $line, $matches)) {
                $milestone['title'] = trim($matches[1]);
                continue;
            }
            
            // Milestone Theme
            if (preg_match('/^\*\*Theme:\*\*\s*(.+)$/', $line, $matches)) {
                $milestone['theme'] = trim($matches[1]);
                continue;
            }
            
            // Activity Start
            if (preg_match('/^### Activity ([\d\.]+)\s*[—\-]\s*(.+)$/', $line, $matches)) {
                if ($currentActivity) {
                    $milestone['activities'][] = $currentActivity;
                }
                
                $currentActivity = [
                    'identifier' => trim($matches[1]), // e.g., "1.1"
                    'title' => trim($matches[2]),
                    'type' => 'project',
                    'difficulty' => 'beginner',
                    'points' => 10,
                    'deadline_days' => 7,
                    'grace_period_days' => 0,
                    'late_penalty_percent' => 0,
                    'chain_parent' => null,
                    'prerequisites' => [],
                    'evidence_requirements' => [],
                    'resources' => [],
                    'description' => '',
                    'evaluation_rubric' => [],
                    'interview_config' => null,
                    'is_required' => true,
                ];
                $inRubric = false;
                $inPrompt = false;
                continue;
            }
            
            // If we are parsing an activity
            if ($currentActivity) {
                // Type
                if (preg_match('/^\-\s*\*\*(?:Activity )?Type:\*\*\s*`?([a-zA-Z_ ]+)`?/i', $line, $matches)) {
                    $rawType = trim(strtolower(str_replace(' ', '_', $matches[1])));
                    if ($rawType === 'learning') {
                        $rawType = 'research';
                    }
                    if ($rawType === 'technical_research') {
                        $rawType = 'research';
                    }
                    $currentActivity['type'] = $rawType;
                    $inPrompt = false;
                    continue;
                }
                
                // Difficulty
                if (preg_match('/^\-\s*\*\*Difficulty:\*\*\s*(.+)$/', $line, $matches)) {
                    $currentActivity['difficulty'] = strtolower(trim($matches[1]));
                    $inPrompt = false;
                    continue;
                }
                
                // Base Points
                if (preg_match('/^\-\s*\*\*Base Points:\*\*\s*(\d+)/', $line, $matches)) {
                    $currentActivity['points'] = (int)$matches[1];
                    $inPrompt = false;
                    continue;
                }
                
                // Deadlines
                if (preg_match('/^\-\s*\*\*Deadline:\*\*\s*(\d+)\s*days?\s*\|\s*\*\*Grace Period:\*\*\s*(\d+)\s*days?\s*\|\s*\*\*Late Penalty:\*\*\s*(\d+)%?(.*)$/', $line, $matches)) {
                    $currentActivity['deadline_days'] = (int)$matches[1];
                    $currentActivity['grace_period_days'] = (int)$matches[2];
                    $currentActivity['late_penalty_percent'] = (int)$matches[3];
                    
                    if (stripos($matches[4], 'optional') !== false) {
                        $currentActivity['is_required'] = false;
                    }
                    $inPrompt = false;
                    continue;
                }
                
                // Chain Parent & Prerequisites
                if (preg_match('/^\-\s*\*\*Chain Parent:\*\*\s*(.+?)\s*\|\s*\*\*Prerequisites:\*\*\s*(.+)$/', $line, $matches)) {
                    $parentRaw = trim($matches[1]);
                    $prereqsRaw = trim($matches[2]);
                    
                    if (strtolower($parentRaw) !== 'none') {
                        // Extract "1.6" from "Activity 1.6"
                        if (preg_match('/Activity\s*([\d\.]+)/i', $parentRaw, $pMatch)) {
                            $currentActivity['chain_parent'] = $pMatch[1];
                        }
                    }
                    
                    if (strtolower($prereqsRaw) !== 'none') {
                        preg_match_all('/Activity\s*([\d\.]+)/i', $prereqsRaw, $prMatches);
                        if (!empty($prMatches[1])) {
                            $currentActivity['prerequisites'] = $prMatches[1];
                        }
                    }
                    $inPrompt = false;
                    continue;
                }
                
                // Evidence Required
                if (preg_match('/^\-\s*\*\*Evidence Required:\*\*\s*(.+)$/', $line, $matches)) {
                    $evidenceRaw = strtolower($matches[1]);
                    if (str_contains($evidenceRaw, 'url') || str_contains($evidenceRaw, 'link')) {
                        $currentActivity['evidence_requirements'][] = 'url';
                    }
                    if (str_contains($evidenceRaw, 'file') || str_contains($evidenceRaw, 'upload')) {
                        $currentActivity['evidence_requirements'][] = 'file_upload';
                    }
                    if (str_contains($evidenceRaw, 'text')) {
                        $currentActivity['evidence_requirements'][] = 'text';
                    }
                    if (str_contains($evidenceRaw, 'video')) {
                        if (!in_array('video', $currentActivity['evidence_requirements'])) {
                            $currentActivity['evidence_requirements'][] = 'video';
                        }
                    }
                    $inPrompt = false;
                    continue;
                }
                
                // Interview Mode
                if (preg_match('/^\-\s*\*\*Interview Mode:\*\*\s*(.+)$/', $line, $matches)) {
                    $modeRaw = strtolower($matches[1]);
                    $mode = 'ai';
                    if (str_contains($modeRaw, 'human')) $mode = 'human';
                    if (str_contains($modeRaw, 'peer')) $mode = 'peer';
                    
                    if (!$currentActivity['interview_config']) {
                        $currentActivity['interview_config'] = [];
                    }
                    $currentActivity['interview_config']['mode'] = $mode;
                    $currentActivity['interview_config']['type'] = 'mock_interview';
                    $inPrompt = false;
                    continue;
                }
                
                // Passing Score
                if (preg_match('/^\-\s*\*\*Passing Score:\*\*\s*(\d+)/', $line, $matches)) {
                    if (!$currentActivity['interview_config']) {
                        $currentActivity['interview_config'] = [];
                    }
                    $currentActivity['interview_config']['min_score'] = (int)$matches[1];
                    $inPrompt = false;
                    continue;
                }
                
                // Required Sessions
                if (preg_match('/^\-\s*\*\*Required Sessions:\*\*\s*(\d+)/', $line, $matches)) {
                    if (!$currentActivity['interview_config']) {
                        $currentActivity['interview_config'] = [];
                    }
                    $currentActivity['interview_config']['count'] = (int)$matches[1];
                    $inPrompt = false;
                    continue;
                }
                
                // Resources
                if (preg_match('/^\-\s*\*\*Resources:\*\*\s*(.+)$/', $line, $matches)) {
                    // Extract urls (can be comma or space separated if they are valid URLs, but assume comma-separated list of links)
                    $urls = array_filter(array_map('trim', explode(',', $matches[1])));
                    if (!isset($currentActivity['resources'])) {
                        $currentActivity['resources'] = [];
                    }
                    
                    $structuredResources = [];
                    foreach ($urls as $url) {
                        $type = (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) ? 'youtube' : 'link';
                        $structuredResources[] = [
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'type' => $type,
                            'title' => $type === 'youtube' ? 'Video Lesson' : 'Reference Article',
                            'content' => $url
                        ];
                    }
                    
                    $currentActivity['resources'] = array_merge($currentActivity['resources'], $structuredResources);
                    $inPrompt = false;
                    continue;
                }
                
                // Prompt
                if (preg_match('/^\-\s*\*\*Prompt:\*\*\s*(.+)$/', $line, $matches)) {
                    $currentActivity['description'] = trim($matches[1]);
                    $inRubric = false;
                    $inPrompt = true;
                    continue;
                }
                
                // Focus (For interviews, append to description)
                if (preg_match('/^\-\s*\*\*Focus:\*\*\s*(.+)$/', $line, $matches)) {
                    $currentActivity['description'] .= "\n\n**Focus:** " . trim($matches[1]);
                    $inPrompt = true;
                    continue;
                }

                // Video Requirement (append to description)
                if (preg_match('/^\-\s*\*\*Video Requirement:\*\*\s*(.+)$/', $line, $matches)) {
                    $currentActivity['description'] .= "\n\n**Video Requirement:** " . trim($matches[1]);
                    $inPrompt = true;
                    continue;
                }
                
                // Rubric start
                if (preg_match('/^\-\s*\*\*Rubric:\*\*/', $line)) {
                    $inRubric = true;
                    $inPrompt = false;
                    continue;
                }
                
                // Rubric items
                if ($inRubric && preg_match('/^\s*\d+\.\s*\*([^*]+)\*\s*\((\d+)%\)\s*[—\-]\s*(.+)$/', $line, $matches)) {
                    $currentActivity['evaluation_rubric'][] = [
                        'criterion' => trim($matches[1]),
                        'weight' => (int)$matches[2],
                        'description' => trim($matches[3]),
                    ];
                    continue;
                }
                
                // If it's a summary or end marker, break
                if (preg_match('/^## Milestone \d+ Summary/', $line) || preg_match('/^## Notes /', $line)) {
                    break;
                }
                
                // Continuation of Prompt/description if not matching anything else
                // This is a naive catch-all, only add if it's text and we're not in rubric
                if ($inPrompt && trim($line) !== '' && !preg_match('/^\- \*\*/', $line) && !preg_match('/^---/', $line)) {
                    if (isset($currentActivity['description']) && $currentActivity['description'] !== '') {
                        $currentActivity['description'] .= " " . trim($line);
                    }
                }
            }
        }
        
        if ($currentActivity) {
            $milestone['activities'][] = $currentActivity;
        }
        
        return $milestone;
    }
}
