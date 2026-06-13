<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final class AppointmentAccessPolicy
{
	/** @param list<AppointmentPolicyRule> $rules */
	private function __construct(
		private bool $endTimeEnabled,
		private array $rules
	) {
	}

	public static function defaults(): self
	{
		return new self(endTimeEnabled: true, rules: []);
	}

	public static function fromDocument(mixed $document): self
	{
		$policy = self::defaults();
		if (!is_array($document)) {
			return $policy;
		}

		$defaults = is_array($document['defaults'] ?? null) ? $document['defaults'] : [];
		if (array_key_exists(AppointmentCapability::EndTimeUse->value, $defaults)) {
			$policy->endTimeEnabled = (bool) $defaults[AppointmentCapability::EndTimeUse->value];
		}

		$rules = [];
		$ruleDocuments = is_array($document['rules'] ?? null) ? $document['rules'] : [];
		foreach ($ruleDocuments as $candidate) {
			$rule = AppointmentPolicyRule::fromDocument($candidate);
			if ($rule !== null) {
				$rules[] = $rule;
			}
		}
		$policy->rules = $rules;

		return $policy;
	}

	public function defaultDocument(): array
	{
		return [
			'defaults' => [
				AppointmentCapability::EndTimeUse->value => $this->endTimeEnabled,
			],
			'rules' => [],
		];
	}

	public function endTimeEnabled(): bool
	{
		return $this->endTimeEnabled;
	}

	/**
	 * @param list<string> $roleIds
	 */
	public function decide(
		AppointmentCapability $capability,
		AppointmentPolicyContext $context,
		int $userId,
		array $roleIds,
		bool $permissionBaseline
	): AppointmentPolicyDecision {
		$decision = $capability === AppointmentCapability::EndTimeUse
			? new AppointmentPolicyDecision($this->endTimeEnabled, 'account_default')
			: new AppointmentPolicyDecision($permissionBaseline, 'permission_baseline');
		$winningSpecificity = null;

		foreach ($this->rules as $rule) {
			$specificity = $rule->specificityFor($capability, $userId, $roleIds, $context);
			if ($specificity === null) {
				continue;
			}

			$effect = $rule->effectFor($capability);
			if ($effect === null || !$this->wins($specificity, $effect, $winningSpecificity, $decision)) {
				continue;
			}

			$decision = new AppointmentPolicyDecision(
				allowed: $effect === AppointmentRuleEffect::Allow,
				source: 'rule',
				ruleId: $rule->id,
				ruleName: $rule->name === '' ? null : $rule->name
			);
			$winningSpecificity = $specificity;
		}

		return $decision;
	}

	private function wins(
		array $candidate,
		AppointmentRuleEffect $effect,
		?array $winning,
		AppointmentPolicyDecision $decision
	): bool {
		if ($winning === null || $this->compareSpecificity($candidate, $winning) > 0) {
			return true;
		}

		return $this->compareSpecificity($candidate, $winning) === 0
			&& $effect === AppointmentRuleEffect::Deny
			&& $decision->allowed;
	}

	private function compareSpecificity(array $left, array $right): int
	{
		foreach ([0, 1, 2, 3] as $index) {
			$comparison = ($left[$index] ?? 0) <=> ($right[$index] ?? 0);
			if ($comparison !== 0) {
				return $comparison;
			}
		}

		return 0;
	}
}
