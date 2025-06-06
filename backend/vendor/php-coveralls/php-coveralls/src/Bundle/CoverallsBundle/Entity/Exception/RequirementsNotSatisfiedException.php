<?php

namespace PhpCoveralls\Bundle\CoverallsBundle\Entity\Exception;

/**
 * Requirements of json_file are not satisfied.
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
class RequirementsNotSatisfiedException extends \RuntimeException
{
    /**
     * Error message.
     *
     * @var string
     */
    protected $message = 'Requirements are not satisfied.';

    /**
     * Read environment variables.
     *
     * @var array
     */
    protected $readEnv;

    /**
     * Array of secret env vars.
     *
     * @var string[]
     */
    private static $secretEnvVars = [
        'COVERALLS_REPO_TOKEN',
    ];

    /**
     * Return help message.
     *
     * @return string
     */
    public function getHelpMessage()
    {
        $message = $this->message."\n";

        if (\is_array($this->readEnv)) {
            foreach ($this->readEnv as $envVarName => $value) {
                $message .= $this->format($envVarName, $value);
            }
        }

        $message .= <<< 'EOL'

Set environment variables properly like the following.
For Travis users:

  - TRAVIS
  - TRAVIS_BUILD_NUMBER
  - TRAVIS_JOB_ID

For CircleCI users:

  - CIRCLECI
  - CIRCLE_WORKFLOW_ID
  - COVERALLS_REPO_TOKEN

For Jenkins users:

  - JENKINS_URL
  - BUILD_NUMBER
  - COVERALLS_REPO_TOKEN

For AppVeyor users:

  - APPVEYOR
  - APPVEYOR_BUILD_NUMBER

For Github Actions users:
  - GITHUB_REF
  - GITHUB_ACTIONS
  - GITHUB_RUN_ID
  - GITHUB_EVENT_NAME
  - COVERALLS_REPO_TOKEN

From local environment:

  - COVERALLS_RUN_LOCALLY
  - COVERALLS_REPO_TOKEN

EOL;

        return $message;
    }

    /**
     * Set read environment variables.
     *
     * @param array $readEnv read environment variables
     */
    public function setReadEnv(array $readEnv)
    {
        $this->readEnv = $readEnv;
    }

    /**
     * Return read environment variables.
     *
     * @return array
     */
    public function getReadEnv()
    {
        return $this->readEnv;
    }

    /**
     * Format a pair of the envVarName and the value.
     *
     * @param string $key   the env var name
     * @param string $value the value of the env var
     *
     * @return string
     */
    protected function format($key, $value)
    {
        if (\in_array($key, self::$secretEnvVars, true)
            && \is_string($value)
            && '' !== $value) {
            $value = '********(HIDDEN)';
        }

        return \sprintf("  - %s=%s\n", $key, var_export($value, true));
    }
}
