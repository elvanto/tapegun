<?php

namespace Tapegun;

class Proc
{
    /**
     * @var resource
     */
    private $pd;

    /**
     * @var array
     */
    private $pipes;

    /**
     * @var string
     */
    private $output = '';

    /**
     * @var int
     */
    private $status = -1;

    /**
     * Proc constructor.
     *
     * @param string $cmd
     * @param string $cwd
     */
    function __construct(string $cmd, string $cwd)
    {
        $this->pd = proc_open(
            $cmd,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $this->pipes,
            $cwd
        );

        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }
    }

    /**
     * Blocks until the provided array of processes have completed.
     *
     * @param Proc[] $procs
     */
    public static function wait(array $procs = [])
    {
        foreach ($procs as $proc) {
            $proc->close();
        }
    }

    /**
     * Returns the stdout stream.
     *
     * @return resource
     */
    public function getStdout()
    {
        return $this->pipes[1];
    }

    /**
     * Returns the stderr stream.
     *
     * @return resource
     */
    public function getStderr()
    {
        return $this->pipes[2];
    }

    /**
     * Returns all collected output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the exit status, or -1 if the process is still running.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Blocks until the process has completed and returns the
     * exit status.
     *
     * @return int
     */
    public function close()
    {
        do {
            $status = proc_get_status($this->pd);
            usleep(5000);
            foreach ($this->pipes as $pipe) {
                if ($content = trim(stream_get_contents($pipe))) {
                    $this->output .= $content;
                }
            }
        } while($status['running']);

        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($this->pd);

        return $this->status = $status['exitcode'];
    }
}