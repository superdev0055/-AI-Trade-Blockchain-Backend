<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum CaseSubjectTypeEnum: string
{
    use EnumTrait;

    /**
     * @color red
     */
    case MoneyProblem = 'MoneyProblem';
    /**
     * @color blue
     */
    case AuthenticationProblem = 'AuthenticationProblem';
    /**
     * @color yellow
     */
    case WebsiteProblem = 'WebsiteProblem';
    /**
     * @color green
     */
    case FinancialProblem = 'FinancialProblem';
    /**
     * @color pink
     */
    case ReportSuggestion = 'ReportSuggestion';
    /**
     * @color gray
     */
    case Other = 'Other';
}
