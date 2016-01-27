<?php
/**
 * 错误处理程序
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-19 17:58:51
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2015-12-19 18:03:39
 */
namespace App\Model;

use Exception;

class ErrorHandler extends \Slim\Handlers\Error {
    /**
     * {@inheritdoc}
     */
    protected function renderHtmlException(Exception $exception) {
        return $this->renderJsonErrorMessage($exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderXmlErrorMessage(Exception $exception) {
        return $this->renderJsonErrorMessage($exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderJsonErrorMessage(Exception $exception) {
        $error = [
            'message' => 'Cncn Application Error',
        ];

        if ($this->displayErrorDetails) {
            $error['exception'] = [];

            do {
                $error['exception'][] = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                ];
            } while ($exception = $exception->getPrevious());
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }
}
