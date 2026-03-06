<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Application\DTOs\FormListResponse;
use Contexis\Events\Form\Domain\AttendeeForm;
use Contexis\Events\Form\Domain\BookingForm;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\Fields\FormFieldCollection;
use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;

final class FakeFormRepository implements FormRepository
{
    /** @var array<int, Form> */
    private array $formsById = [];

    public ?FormId $lastFindArg = null;
    public ?FormCriteria $lastCriteria = null;

    public function __construct(Form ...$forms)
    {
        foreach ($forms as $form) {
            $this->formsById[$form->id->toInt()] = $form;
        }
    }

    public static function empty(): self
    {
        return new self();
    }

    public function ensureBookingForm(FormId $formId, ?string $name = null): void
    {
        $this->formsById[$formId->toInt()] = new BookingForm(
            id: $formId,
            type: FormType::BOOKING,
            fields: new FormFieldCollection(),
            name: $name ?? 'Fake Booking Form',
            description: 'Fake booking form for tests'
        );
    }

    public function ensureAttendeeForm(FormId $formId, ?string $name = null): void
    {
        $this->formsById[$formId->toInt()] = new AttendeeForm(
            id: $formId,
            type: FormType::ATTENDEE,
            fields: new FormFieldCollection(),
            name: $name ?? 'Fake Attendee Form',
            description: 'Fake attendee form for tests'
        );
    }

    public function find(FormId $formId): ?Form
    {
        $this->lastFindArg = $formId;

        return $this->formsById[$formId->toInt()] ?? null;
    }

    public function findByCriteria(FormCriteria $criteria): FormListResponse
    {
        $this->lastCriteria = $criteria;

        return new FormListResponse();
    }

    public function saveStatus(FormId $formId, Status $status): void
    {
    }

    public function delete(FormId $formId): bool
    {
        $key = $formId->toInt();
        if (!isset($this->formsById[$key])) {
            return false;
        }

        unset($this->formsById[$key]);
        return true;
    }

    public function duplicate(FormId $formId): ?FormId
    {
        $form = $this->find($formId);
        if ($form === null) {
            return null;
        }

        $newId = FormId::from($this->nextId());
        if ($newId === null) {
            return null;
        }

        $copy = $form instanceof BookingForm
            ? new BookingForm($newId, $form->type, $form->fields, $form->name . ' Copy', $form->description)
            : new AttendeeForm($newId, $form->type, $form->fields, $form->name . ' Copy', $form->description);

        $this->formsById[$newId->toInt()] = $copy;

        return $newId;
    }

    public function getCountsByStatus(): StatusCounts
    {
        return new StatusCounts(publish: count($this->formsById));
    }

    private function nextId(): int
    {
        if ($this->formsById === []) {
            return 1;
        }

        return max(array_keys($this->formsById)) + 1;
    }
}
