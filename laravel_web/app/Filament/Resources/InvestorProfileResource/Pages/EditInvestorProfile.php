<?php

namespace App\Filament\Resources\InvestorProfileResource\Pages;

use App\Filament\Resources\InvestorProfileResource;
use App\Models\InvestorProfile;
use App\Services\InvestorComplianceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvestorProfile extends EditRecord
{
    protected static string $resource = InvestorProfileResource::class;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $user = auth()->user();
        /** @var InvestorProfile $record */
        $record = $this->getRecord();

        if (!$record->isEditAuthorized($user)) {
            Notification::make()
                ->title('Internal Policy: Supervisor Approval Required')
                ->body('In accordance with RideMyCars Internal Compliance Policy, an Admin cannot edit an Investor Profile without prior approval from a Supervisor.')
                ->danger()
                ->persistent()
                ->send();

            $this->redirect(InvestorProfileResource::getUrl('index'));
        }
    }

    protected function afterSave(): void
    {
        $user = auth()->user();
        /** @var InvestorProfile $record */
        $record = $this->getRecord();

        InvestorComplianceService::logAction(
            $record->id,
            $user->id,
            'PROFILE_MODIFIED',
            "Investor profile modified by {$user->name} ({$user->email}) under supervisor clearance status: {$record->edit_approval_status}."
        );

        // Reset approval status if the editing user is a non-supervisor admin
        if (!InvestorProfile::isUserSupervisor($user)) {
            $record->edit_approval_status = 'NONE';
            $record->saveQuietly();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
