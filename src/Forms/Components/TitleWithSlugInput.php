<?php

namespace Camya\Filament\Forms\Components;

use Camya\Filament\Forms\Fields\SlugInput;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TitleWithSlugInput
{
    public static function make(

        // Model fields
        ?string $fieldTitle = null,
        ?string $fieldSlug = null,

        // Url
        string|Closure|null $urlPath = '/',
        string|Closure|null $urlHost = null,
        bool $urlHostVisible = true,
        bool|Closure $urlVisitLinkVisible = true,
        null|Closure|string $urlVisitLinkLabel = null,
        ?Closure $urlVisitLinkRoute = null,

        // Title
        string|Closure|null $titleLabel = null,
        ?string $titlePlaceholder = null,
        array|Closure|null $titleExtraInputAttributes = null,
        array $titleRules = [
            'required',
        ],
        array $titleRuleUniqueParameters = [],
        bool|Closure $titleIsReadonly = false,
        bool|Closure $titleAutofocus = true,
        ?Closure $titleAfterStateUpdated = null,

        // Slug
        ?string $slugLabel = null,
        array $slugRules = [
            'required',
        ],
        array $slugRuleUniqueParameters = [],
        bool|Closure $slugIsReadonly = false,
        ?Closure $slugAfterStateUpdated = null,
        ?Closure $slugSlugifier = null,
        string|Closure|null $slugRuleRegex = '/^[a-z0-9\-\_]*$/',
        string|Closure|null $slugLabelPostfix = null,
    ): Group {
        $fieldTitle = $fieldTitle ?? config('filament-title-with-slug.field_title');
        $fieldSlug = $fieldSlug ?? config('filament-title-with-slug.field_slug');
        $urlHost = $urlHost ?? config('filament-title-with-slug.url_host');

        /** Input: "Title" */
        $textInput = TextInput::make($fieldTitle)
            ->disabled($titleIsReadonly)
            ->autofocus($titleAutofocus)
            ->live()
            ->disableAutocomplete()
            ->rules($titleRules)
            ->extraInputAttributes($titleExtraInputAttributes ?? ['class' => 'text-xl font-semibold'])
            ->beforeStateDehydrated(fn (TextInput $component, $state) => $component->state(trim($state)))
            ->afterStateUpdated(
                function (
                    Get $get,
                    Set $set,
                    ?string $old,
                    ?string $state,
                    ?Model $record,
                    TextInput $component
                ) use (
                    $slugSlugifier,
                    $fieldSlug,
                    $titleAfterStateUpdated,
                ) {
                    $currentSlug = (string) ($get($fieldSlug) ?? '');
                    $generatedSlugFromPreviousTitle = self::slugify($slugSlugifier, $old);
                    $shouldAutoUpdateSlug = blank($currentSlug) || ($currentSlug === $generatedSlugFromPreviousTitle);

                    if (
                        $shouldAutoUpdateSlug &&
                        filled($state)
                    ) {
                        $set($fieldSlug, self::slugify($slugSlugifier, $state));
                    }

                    if ($titleAfterStateUpdated) {
                        $component->evaluate($titleAfterStateUpdated);
                    }
                }
            );

        if (in_array('required', $titleRules, true)) {
            $textInput->required();
        }

        if ($titlePlaceholder !== '') {
            $textInput->placeholder($titlePlaceholder ?: fn () => Str::of($fieldTitle)->headline());
        }

        if (! $titleLabel) {
            $textInput->disableLabel();
        }

        if ($titleLabel) {
            $textInput->label($titleLabel);
        }

        if ($titleRuleUniqueParameters) {
            $textInput->unique(...$titleRuleUniqueParameters);
        }

        /** Input: "Slug" (+ view) */
        $slugInput = SlugInput::make($fieldSlug)

            // Custom SlugInput methods
            ->slugInputVisitLinkRoute($urlVisitLinkRoute)
            ->slugInputVisitLinkLabel($urlVisitLinkLabel)
            ->slugInputUrlVisitLinkVisible($urlVisitLinkVisible)
            ->slugInputContext(fn ($context) => $context === 'create' ? 'create' : 'edit')
            ->slugInputRecordSlug(fn (?Model $record) => $record?->getAttributeValue($fieldSlug))
            ->slugInputModelName(
                fn (?Model $record) => $record
                    ? Str::of(class_basename($record))->headline()
                    : ''
            )
            ->slugInputLabelPrefix($slugLabel)
            ->slugInputBasePath($urlPath)
            ->slugInputBaseUrl($urlHost)
            ->slugInputShowUrl($urlHostVisible)
            ->slugInputSlugLabelPostfix($slugLabelPostfix)

            // Default TextInput methods
            ->readOnly($slugIsReadonly)
            ->live()
            ->disableAutocomplete()
            ->disableLabel()
            ->regex($slugRuleRegex)
            ->rules($slugRules)
            ->afterStateUpdated(
                function (
                    Get $get,
                    Set $set,
                    ?string $state,
                    TextInput $component
                ) use (
                    $slugSlugifier,
                    $fieldTitle,
                    $fieldSlug,
                    $slugAfterStateUpdated,
                ) {
                    $text = trim($state) === ''
                        ? $get($fieldTitle)
                        : $state;

                    $set($fieldSlug, self::slugify($slugSlugifier, $text));

                    if ($slugAfterStateUpdated) {
                        $component->evaluate($slugAfterStateUpdated);
                    }
                }
            );

        if (in_array('required', $slugRules, true)) {
            $slugInput->required();
        }

        $slugRuleUniqueParameters
            ? $slugInput->unique(...$slugRuleUniqueParameters)
            : $slugInput->unique(ignorable: fn (?Model $record) => $record);

        /** Group */

        return Group::make()
            ->schema([
                $textInput,
                $slugInput,
            ]);
    }

    /** Fallback slugifier, over-writable with slugSlugifier parameter. */
    protected static function slugify(?Closure $slugifier, ?string $text): string
    {
        if (is_null($text) || ! trim($text)) {
            return '';
        }

        return is_callable($slugifier)
            ? $slugifier($text)
            : Str::slug($text);
    }
}
