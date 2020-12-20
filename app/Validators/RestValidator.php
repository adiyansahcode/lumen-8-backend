<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class RestValidator extends Validator
{
    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return void
     */
    public function addFailure($attribute, $rule, $parameters = [])
    {
        if (!$this->messages) {
            $this->passes();
        }
        $message = $this->getMessage($attribute, $rule);
        $message = $this->makeReplacements($message, $attribute, $rule, $parameters);

        $ruleName = strtolower(Str::snake($rule));
        $code = $this->ruleToCode($ruleName);
        $title = str_replace('_', ' ', $ruleName);

        $customMessage = new MessageBag();
        $customMessage->merge(
            [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => $code,
                'title' => $title,
                'detail' => $message,
                'source' => ['pointer' => '/data/attributes/' . $attribute],
            ]
        );
        $this->messages->add('errors', $customMessage);

        $this->failedRules[$attribute][$rule] = $parameters;
    }

    private function ruleToCode(string $rule): string
    {
        // https://github.com/laravel/laravel/blob/master/resources/lang/en/validation.php

        $map = [
            'accepted' => '001',
            'active_url' => '002',
            'after' => '003',
            'after_or_equal' => '004',
            'alpha' => '005',
            'alpha_dash' => '006',
            'alpha_num' => '007',
            'array' => '008',
            'before' => '009',
            'before_or_equal' => '010',
            'between' => '011',
            'boolean' => '012',
            'confirmed' => '013',
            'date' => '014',
            'date_equals' => '015',
            'date_format' => '016',
            'different' => '017',
            'digits' => '018',
            'digits_between' => '019',
            'dimensions' => '020',
            'distinct' => '021',
            'email' => '022',
            'ends_with' => '023',
            'exists' => '024',
            'file' => '025',
            'filled' => '026',
            'gt' => '027',
            'gte' => '028',
            'image' => '029',
            'in' => '030',
            'in_array' => '031',
            'integer' => '032',
            'ip' => '033',
            'ipv4' => '034',
            'ipv6' => '035',
            'json' => '036',
            'lt' => '037',
            'lte' => '038',
            'max' => '039',
            'mimes' => '040',
            'mimetypes' => '041',
            'min' => '042',
            'multiple_of' => '043',
            'not_in' => '044',
            'not_regex' => '045',
            'numeric' => '046',
            'password' => '047',
            'present' => '048',
            'regex' => '049',
            'required' => '050',
            'required_if' => '051',
            'required_unless' => '052',
            'required_with' => '053',
            'required_with_all' => '054',
            'required_without' => '055',
            'required_without_all' => '056',
            'same' => '057',
            'size' => '058',
            'starts_with' => '059',
            'string' => '060',
            'timezone' => '061',
            'unique' => '062',
            'uploaded' => '063',
            'url' => '064',
            'uuid' => '065',
        ];

        if (isset($map[$rule])) {
            return $map[$rule];
        } else {
            return '400';
        }
    }
}
