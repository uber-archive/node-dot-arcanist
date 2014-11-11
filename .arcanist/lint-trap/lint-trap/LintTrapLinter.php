<?php

/**
 * Uses JSHint to detect errors and potential problems in JavaScript code.
 */
final class LintTrapLinter extends ArcanistExternalLinter {

  private $lintignore;
  private $lintrc;

  public function getInfoName() {
    return 'LintTrap';
  }

  public function getInfoURI() {
    return 'https://github.com/uber/lint-trap';
  }

  public function getInfoDescription() {
    return pht('Use `lint-trap` to detect issues with JavaScript source files.');
  }

  public function getLinterName() {
    return 'LintTrap';
  }

  public function getLinterConfigurationName() {
    return 'lint-trap';
  }

  protected function getDefaultMessageSeverity($code) {
    if (preg_match('/^W/', $code)) {
      return ArcanistLintSeverity::SEVERITY_WARNING;
    } else if (preg_match('/^E043$/', $code)) {
      // TODO: If JSHint encounters a large number of errors, it will quit
      // prematurely and add an additional "Too Many Errors" error. Ideally, we
      // should be able to pass some sort of `--force` option to `jshint`.
      //
      // See https://github.com/jshint/jshint/issues/180
      return ArcanistLintSeverity::SEVERITY_DISABLED;
    } else {
      return ArcanistLintSeverity::SEVERITY_ERROR;
    }
  }

  public function getDefaultBinary() {
    $prefix = $this->getDeprecatedConfiguration('lint.lint-trap.prefix');
    $bin = $this->getDeprecatedConfiguration('lint.lint-trap.bin', 'lint-trap');

    if ($prefix) {
      return $prefix.'/'.$bin;
    } else {
      return $bin;
    }
  }

  public function getVersion() {
    list($stdout, $stderr) = execx(
      '%C --version',
      $this->getExecutableCommand());

    $matches = array();
    $regex = '/^lint-trap v(?P<version>\d+\.\d+\.\d+)$/';
    if (preg_match($regex, $stderr, $matches)) {
      return $matches['version'];
    } else {
      return false;
    }
  }

  public function getInstallInstructions() {
    return pht('Install lint-trap using `npm install lint-trap`.');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  public function supportsReadDataFromStdin() {
    return true;
  }

  public function getReadDataFromStdinFilename() {
    return '-';
  }

  protected function getMandatoryFlags() {
    $options = array();

    $options[] = '--reporter='.dirname(realpath(__FILE__)).'/reporter.js';

    if ($this->jshintrc) {
      $options[] = '--config='.$this->jshintrc;
    }

    if ($this->jshintignore) {
      $options[] = '--exclude-path='.$this->jshintignore;
    }

    return $options;
  }

  public function getLinterConfigurationOptions() {
    $options = array(
      'lint-trap.lintignore' => array(
        'type' => 'optional string',
        'help' => pht('Pass in a custom .lintignore file path.'),
      ),
      'lint-trap.lintrc' => array(
        'type' => 'optional string',
        'help' => pht('Custom .lintrc configuration file.'),
      ),
    );

    return $options + parent::getLinterConfigurationOptions();
  }

  public function setLinterConfigurationValue($key, $value) {
    switch ($key) {
      case 'lint-trap.lintignore':
        $this->lintignore = $value;
        return;

      case 'lint-trap.lintrc':
        $this->lintrc = $value;
        return;
    }

    return parent::setLinterConfigurationValue($key, $value);
  }

  protected function getDefaultFlags() {
    $options = $this->getDeprecatedConfiguration(
      'lint.lint-trap.options',
      array());

    $config = $this->getDeprecatedConfiguration('lint.lint-trap.config');
    if ($config) {
      $options[] = '--config='.$config;
    }

    return $options;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $errors = json_decode($stdout, true);

    if (!is_array($errors)) {
      // Something went wrong and we can't decode the output. Exit abnormally.
      throw new ArcanistUsageException(
        "lint-trap returned unparseable output.\n".
        "stdout:\n\n{$stdout}".
        "stderr:\n\n{$stderr}");
    }

    $messages = array();
    foreach ($errors as $err) {
      $message = new ArcanistLintMessage();
      $message->setPath($path);
      $message->setLine(idx($err, 'line'));
      $message->setChar(idx($err, 'col'));
      $message->setCode(idx($err, 'code'));
      $message->setName('lint-trap'.idx($err, 'code'));
      $message->setDescription(idx($err, 'reason'));
      $message->setSeverity($this->getLintMessageSeverity(idx($err, 'code')));

      $messages[] = $message;
    }

    return $messages;
  }

  protected function getLintCodeFromLinterConfigurationKey($code) {
    if (!preg_match('/^(E|W)\d+$/', $code)) {
      throw new Exception(
        pht(
          'Unrecognized lint message code "%s". Expected a valid lint-trap '.
          'lint code like "%s" or "%s".',
          $code,
          'E033',
          'W093'));
    }

    return $code;
  }

}
