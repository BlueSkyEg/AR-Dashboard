<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\DonationForm;


class DonationFormService
{
    /**
     * @param int $formId
     * @return DonationForm|null
     */
    public function getDonationFormById (int $formId): ?DonationForm
    {
        return DonationForm::find($formId);
    }

    // /**
    //  * @param array $attributes
    //  * @return DonationForm
    //  */
    // public function createDonationForm (array $attributes): DonationForm
    // {
    //     return DonationForm::create($attributes);
    // }

    public function createDonationForm(array $data, Model $existingModel)
    {
        // Check if donation_form_data is provided
        if (isset($data['donation_form_data'])) {
            $donationFormData = $data['donation_form_data'];

            // Validate required fields
            if (isset($donationFormData['title'], $donationFormData['status'], $donationFormData['fully_fund_level'], $donationFormData['levels'])) {
                // Create a new donation form with the provided data
                $donationForm = new DonationForm(); // Replace DonationForm with the actual model name
                $donationForm->title = $donationFormData['title'];
                $donationForm->status = $donationFormData['status'];
                $donationForm->fully_fund_level = $donationFormData['fully_fund_level'];
                $donationForm->levels = json_encode($donationFormData['levels']); // Assuming levels is an array, you can encode it to JSON

                // Save the new donation form
                $donationForm->save();

                // Optionally, update the existing model to associate with the new donation form
                $existingModel->donation_form_id = $donationForm->id;
                $existingModel->save();

                // Return the donation_form_id from the updated model
                return $existingModel->donation_form_id;

            } else {
                throw new Exception('Invalid donation form data provided. Required fields: title, status, fully_fund_level, levels.');
            }
        }
    }

}
