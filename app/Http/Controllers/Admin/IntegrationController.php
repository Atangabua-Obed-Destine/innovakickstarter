<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Integrations\IntegrationSettings;
use App\Services\Integrations\IntegrationTester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Integrations page: keys and settings for OpenAI, GitHub and email,
 * with a test button per service.
 */
class IntegrationController extends Controller
{
    public function __construct(
        protected IntegrationSettings $settings,
        protected IntegrationTester $tester,
    ) {
    }

    public function index(): View
    {
        $groups = [];
        foreach (IntegrationSettings::GROUPS as $group => $meta) {
            $groups[$group] = $meta + ['rows' => $this->settings->status($group)];
        }

        return view('admin.integrations.index', ['groups' => $groups]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(IntegrationSettings::groupExists($group), 404);
        $this->validateGroup($request, $group);

        $changes = $this->settings->save($group, $request->input('values', []), $request->input('remove', []), $request->user());
        $title = IntegrationSettings::GROUPS[$group]['title'];

        return redirect()->to(route('admin.integrations.index') . '#' . $group)
            ->with('success', $changes ? "{$title} settings saved." : "No changes to {$title} settings.");
    }

    /**
     * Test the values in the form (blank secrets mean "the current one").
     * Nothing is saved.
     */
    public function test(Request $request, string $group): JsonResponse
    {
        abort_unless(IntegrationSettings::groupExists($group), 404);
        $this->validateGroup($request, $group, $group === 'mail' ? ['test_to' => 'required|email|max:255'] : []);

        $values = $this->settings->valuesForTest($group, $request->input('values', []), $request->input('remove', []));

        return response()->json($this->tester->run($group, $values, ['to' => $request->input('test_to')]));
    }

    protected function validateGroup(Request $request, string $group, array $extra = []): void
    {
        $rules = ['values' => 'array', 'remove' => 'array'];
        foreach (IntegrationSettings::fields($group) as $key => $field) {
            $rules["values.{$key}"] = 'nullable|' . $field['rules'];
        }

        $request->validate($rules + $extra, [], collect(IntegrationSettings::fields($group))
            ->mapWithKeys(fn ($field, $key) => ["values.{$key}" => $field['label']])
            ->all());
    }
}
