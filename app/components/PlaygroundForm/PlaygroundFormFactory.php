<?php declare(strict_types = 1);

namespace App\Components;

use App\Model\CodeValidator;
use App\Model\ConfigValidator;
use App\Model\PhpStanVersions;
use Nette\Application\UI;


class PlaygroundFormFactory
{
	/** @var CodeValidator */
	private $codeValidator;

	/** @var ConfigValidator */
	private $configValidator;

	/** @var PhpStanVersions */
	private $versions;

	/** @var array */
	private $defaults;


	public function __construct(
		CodeValidator $codeValidator,
		ConfigValidator $configValidator,
		PhpStanVersions $versions,
		array $defaults
	) {
		$this->codeValidator = $codeValidator;
		$this->configValidator = $configValidator;
		$this->versions = $versions;
		$this->defaults = $defaults;
	}


	public function create(): UI\Form
	{
		$versionItems = $this->versions->fetch();

		$form = new UI\Form();

		$form->addTextArea('phpCode')
			->setRequired();

		$form->addTextArea('config')
			->setRequired(FALSE);

		$form->addInteger('level')
			->setRequired()
			->addRule($form::MIN, 'Level must be non-negative integer', 0);

		$form->addSelect('version')
			->setItems($versionItems)
			->setRequired();

		$form->addSubmit('send');

		if ($this->defaults) {
			$form->setDefaults([
				'level' => $this->defaults['level'],
				'version' => array_search($this->defaults['versionLabel'], $versionItems, TRUE),
				'phpCode' => $this->defaults['phpCode'],
				'config' => $this->defaults['config'],
			]);
		}

		$form->onValidate[] = function (UI\Form $form, array $values): void {
			foreach ($this->codeValidator->validate($values['phpCode']) as $phpCodeError) {
				$form->addError($phpCodeError);
			}

			foreach ($this->configValidator->validate($values['config']) as $configError) {
				$form->addError($configError);
			}
		};

		return $form;
	}
}