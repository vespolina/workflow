QueueTask
=========

The Queue task is used for tasks in the workflow that could potentially take a large amount of time
or is dependent upon outside services. When the token is accepted into task, the execution() method
pushes the token to the queue. A method with the name of the queue is called when the queue is consumed.

BernardQueue
------------
.. code-block:: php

    <?php

    use Vespolina\Workflow\Task\BernardQueueTask;


    class ApiTask extends BernardQueueTask
    {

        public function callApi()
        {

            // ... do processing

            // if the processing is successful
            $this->postConsume($token);
        }

        public function getQueueName()
        {
            return 'CallApi';
        }
    }
