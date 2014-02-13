<?php
namespace umi\stream\exception;

/**
 * Исключения, связанные с ошибками, которые можно выявить только во время исполнения.
 */
class RuntimeException extends \RuntimeException implements IException
{
}
