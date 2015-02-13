<?php

/**
 * Uses lint-trap to detect errors and potential problems in JavaScript code.
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
    if (preg_match('/^warning/', $code)) {
      return ArcanistLintSeverity::SEVERITY_WARNING;
    } else {
      return ArcanistLintSeverity::SEVERITY_ERROR;
    }
  }

  public function getDefaultBinary() {
    return getcwd().'/node_modules/.bin/lint-trap';
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

  protected function getMandatoryFlags() {
    $options = array(getcwd(), '--reporter=json');
    if ($this->lineLength) {
      $options[] = '--line-length=' . $this->lineLength;
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
      'lint-trap.line-length' => array(
        'type' => 'optional int',
        'help' => pht('Custom maximum line-length'),
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

      case 'lint-trap.line-length':
        $this->lineLength = $value;
        return;
    }

    return parent::setLinterConfigurationValue($key, $value);
  }

  protected function getDefaultFlags() {
    $options = array();
    return $options;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $json = json_decode($stdout, true);
    $files = idx($json, 'files');

    if (!is_array($files)) {
      // Something went wrong and we can't decode the output. Exit abnormally.
      throw new ArcanistUsageException(
        "lint-trap returned unparseable output.\n".
        "stdout:\n\n{$stdout}".
        "stderr:\n\n{$stderr}");
    }

    $messages = array();
    foreach ($files as $f) {
      $errors = idx($f, 'errors');
      foreach ($errors as $err) {
        $message = new ArcanistLintMessage();
        $message->setPath(idx($f, 'file'));
        $message->setLine(idx($err, 'line'));
        $message->setChar(idx($err, 'column'));
        $message->setCode(idx($err, 'rule'));
        $message->setName(idx($err, 'linter').'.'.idx($err, 'rule'));
        $message->setDescription(idx($err, 'message'));
        $message->setSeverity($this->getLintMessageSeverity(idx($err, 'type')));

        $messages[] = $message;
      }
    }

    return $messages;
  }

}
