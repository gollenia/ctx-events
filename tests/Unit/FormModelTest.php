<?php
declare(strict_types=1);

use Contexis\Events\Form\Domain\BookingForm;
use Contexis\Events\Form\Domain\Enums\CheckboxVariant;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\FieldWidth;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\Enums\InputType;
use Contexis\Events\Form\Domain\Enums\NumberVariant;
use Contexis\Events\Form\Domain\Enums\SelectVariant;
use Contexis\Events\Form\Domain\Fields\CheckboxDetails;
use Contexis\Events\Form\Domain\Fields\CountryDetails;
use Contexis\Events\Form\Domain\Fields\DateDetails;
use Contexis\Events\Form\Domain\Fields\FormField;
use Contexis\Events\Form\Domain\Fields\FormFieldCollection;
use Contexis\Events\Form\Domain\Fields\HtmlDetails;
use Contexis\Events\Form\Domain\Fields\InputDetails;
use Contexis\Events\Form\Domain\Fields\NumberDetails;
use Contexis\Events\Form\Domain\Fields\SelectDetails;
use Contexis\Events\Form\Domain\Fields\TextareaDetails;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\ValueObjects\CountryCodes;
use Contexis\Events\Form\Domain\ValueObjects\SelectOptions;

function createComprehensiveBookingForm(): BookingForm
{
    $formId = FormId::from(501);

    if ($formId === null) {
        throw new RuntimeException('Failed to create form id for test.');
    }

    return new BookingForm(
        id: $formId,
        type: FormType::BOOKING,
        name: 'Booking Form Complete',
        description: 'Contains all field types for tests',
        fields: new FormFieldCollection(
            new FormField('full_name', 'Full Name', true, new InputDetails(InputType::TEXT), FieldWidth::SIX),
            new FormField('bio', 'Bio', true, new TextareaDetails(rows: 4), FieldWidth::SIX),
            new FormField(
                'audience',
                'Audience',
                true,
                new SelectDetails(SelectVariant::SELECT, SelectOptions::fromArray(['child', 'adult'])),
                FieldWidth::THREE
            ),
            new FormField('terms', 'Accept Terms', false, new CheckboxDetails(false, null, CheckboxVariant::DEFAULT), FieldWidth::THREE),
            new FormField('note', 'HTML Note', false, new HtmlDetails('<p>Note</p>'), FieldWidth::SIX),
            new FormField('country', 'Country', true, new CountryDetails(CountryCodes::of('AT', 'DE'), '', 'Select country'), FieldWidth::THREE),
            new FormField(
                'birth_date',
                'Birth Date',
                true,
                new DateDetails(
                    defaultValue: null,
                    placeholder: null,
                    earliestDate: new DateTimeImmutable('2020-01-01'),
                    latestDate: new DateTimeImmutable('2030-12-31')
                ),
                FieldWidth::THREE
            ),
            new FormField(
                'tickets_count',
                'Tickets',
                true,
                new NumberDetails(min: 1, max: 10, step: 1, variant: NumberVariant::INPUT),
                FieldWidth::THREE
            )
        )
    );
}

test('creates a form model containing all field types', function () {
    $form = createComprehensiveBookingForm();

    expect($form->id->toInt())->toBe(501);
    expect($form->type)->toBe(FormType::BOOKING);
    expect($form->fields->count())->toBe(8);

    $fields = $form->fields->toArray();
    $types = array_map(
        static fn (FormField $field): string => $field->details->getType()->value,
        $fields
    );
    $names = array_map(
        static fn (FormField $field): string => $field->name,
        $fields
    );

    expect($types)->toContain(FieldType::INPUT->value);
    expect($types)->toContain(FieldType::TEXTAREA->value);
    expect($types)->toContain(FieldType::SELECT->value);
    expect($types)->toContain(FieldType::CHECKBOX->value);
    expect($types)->toContain(FieldType::HTML->value);
    expect($types)->toContain(FieldType::COUNTRY->value);
    expect($types)->toContain(FieldType::DATE->value);
    expect($types)->toContain(FieldType::NUMBER->value);
    expect($names)->toBe([
        'full_name',
        'bio',
        'audience',
        'terms',
        'note',
        'country',
        'birth_date',
        'tickets_count',
    ]);
});

test('validates all form fields for valid and invalid payloads', function () {
    $form = createComprehensiveBookingForm();

    $validPayload = [
        'full_name' => 'Max Mustermann',
        'bio' => 'Hello, this is my bio.',
        'audience' => 'adult',
        'terms' => true,
        'note' => '<p>ignored</p>',
        'country' => 'AT',
        'birth_date' => '2026-06-15',
        'tickets_count' => 3,
    ];

    $validResult = $form->validate($validPayload);

    expect($validResult->isValid)->toBeTrue();
    expect($validResult->errors)->toBe([]);
    expect($validResult->validatedData['full_name'])->toBe('Max Mustermann');
    expect($validResult->validatedData['bio'])->toBe('Hello, this is my bio.');
    expect($validResult->validatedData['audience'])->toBe('adult');
    expect($validResult->validatedData['terms'])->toBeTrue();
    expect($validResult->validatedData['country'])->toBe('AT');
    expect($validResult->validatedData['birth_date'])->toBeInstanceOf(DateTimeImmutable::class);
    expect($validResult->validatedData['tickets_count'])->toBe(3);

    $invalidCases = [
        'full_name' => ['', 'required'],
        'bio' => [123, 'invalid_format'],
        'audience' => ['senior', 'invalid_format'],
        'terms' => ['yes', 'invalid_format'],
        'country' => ['XX', 'invalid_format'],
        'birth_date' => ['not-a-date', 'invalid_format'],
        'tickets_count' => [999, 'too_high'],
    ];

    foreach ($invalidCases as $fieldName => [$invalidValue, $expectedError]) {
        $payload = $validPayload;
        $payload[$fieldName] = $invalidValue;

        $result = $form->validate($payload);

        expect($result->isValid)->toBeFalse();
        expect($result->errors)->toHaveKey($fieldName);
        expect($result->errors[$fieldName])->toBe($expectedError);
    }
});
