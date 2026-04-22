<?php

namespace Tests\Configuration;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * CI guard that fails the Configuration suite the moment any Blade view
 * reintroduces a namespace-qualified Eloquent static call.
 *
 * ## Rule
 *
 * No file under `resources/views/` may contain a pattern of the form
 * `\App\Models\SomeThing::method(...)`.  Class-name constant references
 * (`::class`) are explicitly excluded because they appear legitimately in
 * `modelClass=` component attributes and are NOT Eloquent calls.
 *
 * ## Allow-list
 *
 * The allow-list below is empty by default (all offenders were removed in
 * EPICs 5 and 7).  If a future change deliberately requires a static call
 * (e.g. a bounded enum conversion), add the *full relative path* from the
 * project root and a comment explaining the exception:
 *
 *   'resources/views/example/show.blade.php' => 'reason …',
 *
 * ## How the guard catches a violation
 *
 * When a Blade view contains `\App\Models\Language::orderBy(...)`, the test
 * lists the offending file path, line number, and the matching line content
 * so the developer can identify and fix the regression quickly.
 */
class BladeHasNoEloquentTest extends TestCase
{
    /**
     * Relative paths (from the project root) that are permitted to contain
     * Eloquent static calls.  Keep this empty; add entries only with explicit
     * sign-off and a documented reason.
     *
     * @var array<string, string>
     */
    private array $allowList = [
        // 'resources/views/example/show.blade.php' => 'reason',
    ];

    /**
     * Regex that matches a namespace-qualified Eloquent static call.
     *
     * Matches:  \App\Models\Item::get(
     * Excludes: \App\Models\Item::class  (class-name constant, not a call)
     *
     * The pattern requires a literal backslash before App to distinguish it
     * from unqualified `App\Models\` imports (which Blade files should not
     * have anyway, but we add the shorter-form check below for defence-in-depth).
     */
    private string $fullyQualifiedPattern = '/\\\\App\\\\Models\\\\[A-Z]\\w+::(?!class\\b)/';

    /**
     * Defensive secondary pattern for shorter-form static calls that would
     * appear if someone added a `use App\Models\*` import at the top of a
     * Blade file (which itself is a code-smell but is technically possible).
     *
     * Matches:  Models\Item::get(
     * Excludes: Models\Item::class
     */
    private string $shortFormPattern = '/\\bModels\\\\[A-Z]\\w+::(?!class\\b)/';

    public function test_blade_views_contain_no_eloquent_static_calls(): void
    {
        $viewsPath = base_path('resources/views');
        $bladeFiles = File::allFiles($viewsPath);

        $violations = [];

        foreach ($bladeFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $fullRelativePath = 'resources/views/'.$relativePath;

            if (array_key_exists($fullRelativePath, $this->allowList)) {
                continue;
            }

            $lines = explode("\n", $file->getContents());

            foreach ($lines as $lineNumber => $line) {
                $humanLine = $lineNumber + 1;

                if (preg_match($this->fullyQualifiedPattern, $line)) {
                    $violations[] = "{$fullRelativePath}:{$humanLine} — {$line}";
                }

                if (preg_match($this->shortFormPattern, $line)) {
                    $violations[] = "{$fullRelativePath}:{$humanLine} [short-form] — {$line}";
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Eloquent static call(s) found in Blade views:\n".implode("\n", $violations).
            "\n\nEloquent calls in Blade views are forbidden (see docs/guidelines for the approved pattern).".
            "\nIf this is intentional, add the file to the allow-list in BladeHasNoEloquentTest.",
        );
    }
}
