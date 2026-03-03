<?php

namespace App\Http\Requests;

use App\Tour;
use App\TourDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpsertTourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in([Tour::STATUS_DRAFT, Tour::STATUS_PUBLIC])],
            'updated_at' => $this->isMethod('put')
                ? ['required', 'date']
                : ['nullable', 'date'],
            'tour_dates' => ['nullable', 'array'],
            'tour_dates.*.id' => ['nullable', 'integer', Rule::exists('tour_dates', 'id')],
            'tour_dates.*.start_date' => ['required_with:tour_dates', 'date', 'after_or_equal:today'],
            'tour_dates.*.end_date' => ['required_with:tour_dates', 'date', 'after_or_equal:today'],
            'tour_dates.*.status' => ['nullable', Rule::in([TourDate::STATUS_ENABLED, TourDate::STATUS_DISABLED])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tourDates = $this->input('tour_dates', []);
            $today = Carbon::today();

            foreach ($tourDates as $index => $tourDate) {
                if (empty($tourDate['start_date'])) {
                    continue;
                }

                try {
                    $startDate = Carbon::parse($tourDate['start_date']);
                } catch (\Exception $exception) {
                    continue;
                }

                if ($startDate->lt($today)) {
                    $validator->errors()->add(
                        'tour_dates.' . $index . '.start_date',
                        'Start date must be today or later.'
                    );
                }

                if (empty($tourDate['end_date'])) {
                    continue;
                }

                try {
                    $endDate = Carbon::parse($tourDate['end_date']);
                } catch (\Exception $exception) {
                    continue;
                }

                if ($endDate->lt($today)) {
                    $validator->errors()->add(
                        'tour_dates.' . $index . '.end_date',
                        'End date must be today or later.'
                    );
                }

                if ($endDate->lt($startDate)) {
                    $validator->errors()->add(
                        'tour_dates.' . $index . '.end_date',
                        'End date must be greater than or equal to start date.'
                    );
                }
            }
        });
    }
}
