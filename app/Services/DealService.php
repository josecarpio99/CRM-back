<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use App\Models\Branch;
use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Enums\DealTypeEnum;
use App\Mail\NewAAACotizado;
use App\Mail\NewDealCreated;
use App\Enums\DealStatusEnum;
use App\Mail\NewAAAOpportunity;
use App\Mail\DealOpportunityCreatedByLeadQualifier;

class DealService
{

    public function store(array $dealData, User $user) : Deal
    {
        $contactId = $dealData['contact_id'] ?? null;
        unset($dealData['contact_id']);

        if (isset($dealData['customer'])) {
            $dealData['customer']['owner_id'] = $dealData['owner_id'];

            $customerData = $dealData['customer'];

            $contactData = [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['mobile'],
            ];

            $customer = Customer::create([
                'owner_id' => $dealData['owner_id'],
                'company_name' => $customerData['company_name'],
                'category_id' => $customerData['category_id'],
            ]);

            $contact = $customer->contacts()->create($contactData);

            $contactId = $contact->id;

            $dealData['customer_id'] = $customer->id;

            unset($dealData['customer']);
        }

        // $dealData['type'] ??= DealTypeEnum::Oportunidad->value;
        $dealData['type'] = DealTypeEnum::Cotizado->value;
        $dealData['created_by'] = $user->id;
        $dealData['created_by_lead_qualifier'] = $user->hasRole(RoleEnum::LeadQualifier->value);
        $dealData['status'] = DealStatusEnum::New->value;

        $dealData['converted_to_quote'] = now();
        $dealData['monitoring_tasks'] = $this->getMonitoringTasks();

        $deal = Deal::create($dealData);

        if ($contactId) {
            $deal->contacts()->attach($contactId);
        }

        if (
            $deal->owner_id != $deal->created_by
        ) {
            try {
                \Mail::to($deal->owner)->send(new DealOpportunityCreatedByLeadQualifier($deal));
            } catch (\Throwable $th) {
                //TODO:
            }
        }

        if ($deal->value >= 1_000_000) {
            if ($deal->type == DealTypeEnum::Oportunidad->value) {

                try {
                    \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAAOpportunity($deal));
                } catch (\Throwable $th) {
                    //TODO...
                }
            } else {
                try {
                    \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAACotizado($deal));
                } catch (\Throwable $th) {
                    return response($th->getMessage(), 500);
                    //TODO...
                }
            }

            $owner = $deal->owner;

            if ($owner->role == RoleEnum::Advisor->value) {
                $branch = Branch::where('name', $owner->branch)->first();

                $usersToNotify = User::query()
                    ->where('id', '<>', 44)
                    ->where('id', '<>', $owner->id)
                    ->where('id', '<>', $dealData['created_by'])
                    ->where(function($query) use($branch){
                        $query->where('role', RoleEnum::Admin->value)
                            ->where(function($query) use($branch) {
                                $query->where('branch', $branch->name)
                                    ->orWhereHas('branches', function($query) use($branch) {
                                        $query->where('branch_id', $branch->id);
                                    });
                            });
                    })
                    ->orWhere(function($query) use($owner){
                        $query->where('role', RoleEnum::TeamLeader->value)
                            ->whereHas('assignedUsers', function($query) use($owner) {
                                $query->where('user_id', $owner->id);
                            });
                    })
                    ->get();

                    if ($deal->type == DealTypeEnum::Oportunidad->value) {

                        try {
                            \Mail::to($usersToNotify)->send(new NewAAAOpportunity($deal));
                        } catch (\Throwable $th) {
                            //TODO...
                        }
                    } else {
                        try {
                            \Mail::to($usersToNotify)->send(new NewAAACotizado($deal));
                        } catch (\Throwable $th) {
                            //TODO...
                        }
                    }
            }

        }

        return $deal;
    }

    public function getMonitoringTasks() : array
    {
        return [
            [
                'content' => 'Videollamada / Visita Inicial y Presentar Semblanza Corporativa.',
                'done' => 0,
            ],
            [
                'content' => 'Levantamiento de Necesidades Completo en Visita o Videollamada.',
                'done' => 0,
            ],
            [
                'content' => 'Grupo de WhatsApp con Gerente o Director.',
                'done' => 0,
            ],
            [
                'content' => 'Compartir Referencia de Proyectos Similares.',
                'done' => 0,
            ],
            [
                'content' => 'Compartir Póliza de Garantía y Catálogo Digital.',
                'done' => 0,
            ],
            [
                'content' => 'Otorgar Descuento "Especial".',
                'done' => 0,
            ],
            [
                'content' => 'Presentación de Propuesta en Showroom, Visita o Videoconferencia. (Idealmente en Showroom)',
                'done' => 0,
            ],
        ];
    }
}
