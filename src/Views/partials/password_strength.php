<?php

use Fin\Narekaltro\Domain\Auth\PasswordPolicy;

$requirements = PasswordPolicy::requirements();
?>
<div class="pw-strength" aria-live="polite">
	<div class="pw-strength-bar"><span class="pw-strength-fill"></span></div>
	<div class="pw-strength-label">Password strength: <strong data-pw-level>—</strong></div>
	<ul class="pw-strength-reqs">
		<?php foreach ($requirements as $requirement): ?>
			<li class="pw-req" data-pattern="<?php echo e($requirement['pattern']); ?>">
				<span class="pw-req-icon" aria-hidden="true"></span>
				<?php echo e($requirement['label']); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<style>
.pw-strength { margin: 6px 0 14px; font-size: 13px; }
.pw-strength-bar { height: 6px; border-radius: 4px; background: #e6e8ec; overflow: hidden; }
.pw-strength-fill { display: block; height: 100%; width: 0; border-radius: 4px; transition: width .25s ease, background .25s ease; background: #d64545; }
.pw-strength-label { margin: 6px 0; color: #6b7280; }
.pw-strength-label strong { color: #374151; }
.pw-strength-reqs { list-style: none; padding: 0; margin: 0; display: grid; gap: 4px; }
.pw-req { display: flex; align-items: center; gap: 8px; color: #6b7280; transition: color .2s ease; }
.pw-req-icon { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd0d8; flex: 0 0 16px; position: relative; transition: all .2s ease; }
.pw-req.met { color: #1f9d55; }
.pw-req.met .pw-req-icon { background: #1f9d55; border-color: #1f9d55; }
.pw-req.met .pw-req-icon::after { content: ""; position: absolute; left: 4px; top: 1px; width: 4px; height: 8px; border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg); }
.pw-strength[data-level="fair"] .pw-strength-fill { background: #e0a83e; }
.pw-strength[data-level="good"] .pw-strength-fill { background: #3b82f6; }
.pw-strength[data-level="strong"] .pw-strength-fill { background: #1f9d55; }
</style>

<script>
(function () {
	var container = document.currentScript.previousElementSibling;
	while (container && !container.classList.contains('pw-strength')) {
		container = container.previousElementSibling;
	}
	if (!container) {
		return;
	}

	var form = container.closest('form');
	var input = form && form.querySelector('input[type="password"][name="password"]');
	if (!input) {
		return;
	}

	var reqs = Array.prototype.map.call(container.querySelectorAll('.pw-req'), function (el) {
		return { el: el, re: new RegExp(el.getAttribute('data-pattern')) };
	});
	var fill = container.querySelector('.pw-strength-fill');
	var levelEl = container.querySelector('[data-pw-level]');
	var levels = ['—', 'Weak', 'Fair', 'Good', 'Strong'];
	var levelKeys = ['', 'weak', 'fair', 'good', 'strong'];

	function evaluate() {
		var value = input.value;
		var met = 0;

		reqs.forEach(function (req) {
			var ok = req.re.test(value);
			req.el.classList.toggle('met', ok);
			if (ok) {
				met += 1;
			}
		});

		// Score 0..6: one point per met requirement, plus bonuses for length & symbols.
		var score = met;
		if (value.length >= 12) {
			score += 1;
		}
		if (/[^A-Za-z0-9]/.test(value)) {
			score += 1;
		}
		if (value === '') {
			score = 0;
		}

		var level = score <= 1 ? 1 : score <= 3 ? 2 : score <= 4 ? 3 : 4;
		if (value === '') {
			level = 0;
		}

		fill.style.width = Math.round((score / 6) * 100) + '%';
		levelEl.textContent = levels[level];
		container.setAttribute('data-level', levelKeys[level]);
	}

	input.addEventListener('input', evaluate);
	evaluate();
})();
</script>
