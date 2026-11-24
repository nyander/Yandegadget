<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Middleware\SetLocale;
use App\Models\User;
use App\Support\Locales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_is_the_default_and_sends_no_translations(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'en')
                ->where('translations', [])
                ->has('locales', count(Locales::all())));
    }

    public function test_choosing_a_language_remembers_it_and_sends_its_wording(): void
    {
        $this->from(route('shop.index'))
            ->post(route('locale.update'), ['locale' => 'tw'])
            ->assertRedirect(route('shop.index'))
            ->assertCookie(SetLocale::COOKIE, 'tw', encrypted: false);

        $this->withUnencryptedCookie(SetLocale::COOKIE, 'tw')
            ->get(route('shop.index'))
            ->assertOk()
            ->assertSee('<html lang="tw"', false)
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'tw')
                ->where('translations.Shop all', Locales::translations('tw')['Shop all']));
    }

    public function test_languages_the_shop_does_not_offer_are_refused_or_ignored(): void
    {
        $this->post(route('locale.update'), ['locale' => 'de'])->assertSessionHasErrors('locale');

        $this->withUnencryptedCookie(SetLocale::COOKIE, 'de')
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
    }

    public function test_the_not_found_page_uses_the_chosen_language(): void
    {
        config(['app.debug' => false]);

        $this->withUnencryptedCookie(SetLocale::COOKIE, 'fr')
            ->get('/no-such-page')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error')
                ->where('locale', 'fr'));
    }

    public function test_the_admin_panel_stays_in_english(): void
    {
        $admin = User::factory()->create()->assignRole(Role::Admin->value);

        $this->actingAs($admin)
            ->withUnencryptedCookie(SetLocale::COOKIE, 'fr')
            ->get('/admin')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertDontSee('lang="fr"', false);
    }

    /**
     * Every phrase the React pages mark with t('…') or tk('…') must be in
     * each language file, with the same :placeholders.
     */
    public function test_every_marked_phrase_is_translated_into_every_language(): void
    {
        $phrases = $this->markedPhrases();
        $this->assertGreaterThan(100, count($phrases));

        foreach (array_keys(Locales::all()) as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $lines = Locales::translations($locale);
            $missing = array_values(array_diff($phrases, array_keys($lines)));
            $this->assertSame([], $missing, "lang/{$locale}.json is missing these phrases");

            foreach ($lines as $english => $translated) {
                $this->assertNotSame('', trim($translated), "{$locale}: empty translation for “{$english}”");
                $this->assertEqualsCanonicalizing(
                    $this->placeholders($english),
                    $this->placeholders($translated),
                    "{$locale}: placeholders differ for “{$english}”",
                );
            }
        }
    }

    /** @return list<string> */
    private function markedPhrases(): array
    {
        $phrases = [];

        /** @var SplFileInfo $file */
        foreach (File::allFiles(resource_path('js')) as $file) {
            if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
                continue;
            }

            preg_match_all('/\bt[k]?\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s', $file->getContents(), $matches);

            foreach ($matches[2] as $phrase) {
                $phrases[] = stripslashes($phrase);
            }
        }

        return array_values(array_unique($phrases));
    }

    /** @return list<string> */
    private function placeholders(string $text): array
    {
        preg_match_all('/:[a-z]+/', $text, $matches);

        return $matches[0];
    }
}
