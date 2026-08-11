<?php

declare(strict_types=1);

namespace TechWilk\Database\Tests;

use PHPUnit\Framework\TestCase;
use TechWilk\Database\DatabaseErrorMapper;
use TechWilk\Database\Exception\{
    BadFieldException,
    BadValueException,
    CheckConstraintException,
    DatabaseAccessDeniedException,
    DatabaseConnectionException,
    DatabaseDataException,
    DatabaseDeadlockException,
    DatabaseException,
    DatabaseLimitExceededException,
    DatabaseObjectExistsException,
    DatabaseObjectNotFoundException,
    DatabaseQueryException,
    DatabaseReadOnlyException,
    DatabaseTransactionException,
    DatabaseTransactionRollbackException,
    DuplicateDatabaseRecordException,
    EmptyQueryException,
    ForeignKeyConstraintException,
    IntegrityConstraintException,
    InvalidTableException,
    NullConstraintException,
};

class DatabaseErrorMapperTest extends TestCase
{
    /**
     * @dataProvider mappedErrorsProvider
     *
     * @param class-string<DatabaseException> $expectedClass
     * @param class-string<DatabaseException>|null $expectedParent
     */
    public function testCreateExceptionMapsToExpectedType(
        ?string $sqlState,
        int $driverCode,
        string $expectedClass,
        ?string $expectedParent = null
    ): void {
        $exception = DatabaseErrorMapper::createException('test', $sqlState, $driverCode);

        $this->assertInstanceOf($expectedClass, $exception);
        $this->assertSame($driverCode, $exception->getCode());
        $this->assertSame('test', $exception->getMessage());

        if (null !== $expectedParent) {
            $this->assertInstanceOf($expectedParent, $exception);
        }
    }

    public static function mappedErrorsProvider(): array
    {
        return [
            'duplicate errno 1022' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                DuplicateDatabaseRecordException::MYSQL_ER_DUP_KEY,
                DuplicateDatabaseRecordException::class,
                IntegrityConstraintException::class,
            ],
            'duplicate errno 1062' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                DuplicateDatabaseRecordException::MYSQL_ER_DUP_ENTRY,
                DuplicateDatabaseRecordException::class,
                IntegrityConstraintException::class,
            ],
            'duplicate errno 1169' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                DuplicateDatabaseRecordException::MYSQL_ER_DUP_UNIQUE,
                DuplicateDatabaseRecordException::class,
                IntegrityConstraintException::class,
            ],
            'duplicate errno 1586' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                DuplicateDatabaseRecordException::MYSQL_ER_DUP_ENTRY_WITH_KEY_NAME,
                DuplicateDatabaseRecordException::class,
                IntegrityConstraintException::class,
            ],
            'null constraint' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                NullConstraintException::MYSQL_ER_BAD_NULL_ERROR,
                NullConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'foreign key errno 1216' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                ForeignKeyConstraintException::MYSQL_ER_NO_REFERENCED_ROW,
                ForeignKeyConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'foreign key errno 1217' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                ForeignKeyConstraintException::MYSQL_ER_ROW_IS_REFERENCED,
                ForeignKeyConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'foreign key errno 1451' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                ForeignKeyConstraintException::MYSQL_ER_ROW_IS_REFERENCED_2,
                ForeignKeyConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'foreign key errno 1452' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                ForeignKeyConstraintException::MYSQL_ER_NO_REFERENCED_ROW_2,
                ForeignKeyConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'check constraint' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                CheckConstraintException::MYSQL_ER_CHECK_CONSTRAINT_VIOLATED,
                CheckConstraintException::class,
                IntegrityConstraintException::class,
            ],
            'deadlock errno' => [
                DatabaseDeadlockException::SQLSTATE_SERIALIZATION_FAILURE,
                DatabaseDeadlockException::MYSQL_ER_LOCK_DEADLOCK,
                DatabaseDeadlockException::class,
                DatabaseTransactionRollbackException::class,
            ],
            'deadlock sqlstate alone' => [
                DatabaseDeadlockException::SQLSTATE_SERIALIZATION_FAILURE,
                0,
                DatabaseDeadlockException::class,
                DatabaseTransactionRollbackException::class,
            ],
            'residual class 40 is rollback not deadlock' => [
                '40000',
                0,
                DatabaseTransactionRollbackException::class,
                null,
            ],
            'invalid table errno' => [
                InvalidTableException::SQLSTATE_BASE_TABLE_OR_VIEW_NOT_FOUND,
                InvalidTableException::MYSQL_ER_NO_SUCH_TABLE,
                InvalidTableException::class,
                DatabaseObjectNotFoundException::class,
            ],
            'invalid table sqlstate alone' => [
                InvalidTableException::SQLSTATE_BASE_TABLE_OR_VIEW_NOT_FOUND,
                0,
                InvalidTableException::class,
                DatabaseObjectNotFoundException::class,
            ],
            'table exists errno' => [
                DatabaseObjectExistsException::SQLSTATE_BASE_TABLE_OR_VIEW_ALREADY_EXISTS,
                DatabaseObjectExistsException::MYSQL_ER_TABLE_EXISTS_ERROR,
                DatabaseObjectExistsException::class,
                null,
            ],
            'table exists sqlstate alone' => [
                DatabaseObjectExistsException::SQLSTATE_BASE_TABLE_OR_VIEW_ALREADY_EXISTS,
                0,
                DatabaseObjectExistsException::class,
                null,
            ],
            'empty query errno' => [
                DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION,
                EmptyQueryException::MYSQL_ER_EMPTY_QUERY,
                EmptyQueryException::class,
                DatabaseQueryException::class,
            ],
            'ambiguous column not integrity' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                DatabaseQueryException::MYSQL_ER_NON_UNIQ_ERROR,
                DatabaseQueryException::class,
                null,
            ],
            'unknown integrity under 23000' => [
                IntegrityConstraintException::SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION,
                9999,
                IntegrityConstraintException::class,
                null,
            ],
            'unknown database not empty query' => [
                DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION,
                DatabaseObjectNotFoundException::MYSQL_ER_BAD_DB_ERROR,
                DatabaseObjectNotFoundException::class,
                null,
            ],
            'no database selected sqlstate' => [
                DatabaseObjectNotFoundException::SQLSTATE_INVALID_CATALOG_NAME,
                0,
                DatabaseObjectNotFoundException::class,
                null,
            ],
            'no database selected errno' => [
                null,
                DatabaseObjectNotFoundException::MYSQL_ER_NO_DB_ERROR,
                DatabaseObjectNotFoundException::class,
                null,
            ],
            'residual 42000 is query not empty' => [
                DatabaseQueryException::SQLSTATE_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION,
                0,
                DatabaseQueryException::class,
                null,
            ],
            'access denied errno' => [
                DatabaseAccessDeniedException::SQLSTATE_INVALID_AUTHORISATION_SPECIFICATION,
                DatabaseAccessDeniedException::MYSQL_ER_ACCESS_DENIED_ERROR,
                DatabaseAccessDeniedException::class,
                null,
            ],
            'access denied sqlstate alone' => [
                DatabaseAccessDeniedException::SQLSTATE_INVALID_AUTHORISATION_SPECIFICATION,
                0,
                DatabaseAccessDeniedException::class,
                null,
            ],
            'connection CR_CONNECTION_ERROR' => [
                'HY000',
                DatabaseConnectionException::MYSQL_CR_CONNECTION_ERROR,
                DatabaseConnectionException::class,
                null,
            ],
            'connection CR_SERVER_GONE' => [
                'HY000',
                DatabaseConnectionException::MYSQL_CR_SERVER_GONE_ERROR,
                DatabaseConnectionException::class,
                null,
            ],
            'too many connections is limit' => [
                '08004',
                DatabaseLimitExceededException::MYSQL_ER_CON_COUNT_ERROR,
                DatabaseLimitExceededException::class,
                null,
            ],
            'read only sqlstate 25006' => [
                DatabaseReadOnlyException::SQLSTATE_READ_ONLY_SQL_TRANSACTION,
                0,
                DatabaseReadOnlyException::class,
                DatabaseTransactionException::class,
            ],
            'read only errno 1207' => [
                '25000',
                DatabaseReadOnlyException::MYSQL_ER_READ_ONLY_TRANSACTION,
                DatabaseReadOnlyException::class,
                DatabaseTransactionException::class,
            ],
            'read only errno 1290' => [
                'HY000',
                DatabaseReadOnlyException::MYSQL_ER_OPTION_PREVENTS_STATEMENT,
                DatabaseReadOnlyException::class,
                DatabaseTransactionException::class,
            ],
            'residual transaction state not read only' => [
                '25001',
                0,
                DatabaseTransactionException::class,
                null,
            ],
            'data class fallback' => [
                '22001',
                0,
                DatabaseDataException::class,
                null,
            ],
            'connection class fallback' => [
                '08S01',
                0,
                DatabaseConnectionException::class,
                null,
            ],
            'unknown defaults to DatabaseException' => [
                'HY000',
                12345,
                DatabaseException::class,
                null,
            ],
        ];
    }

    public function testParentOfDuplicate(): void
    {
        $this->assertInstanceOf(
            IntegrityConstraintException::class,
            new DuplicateDatabaseRecordException('x')
        );
    }

    public function testParentOfNullConstraint(): void
    {
        $this->assertInstanceOf(
            IntegrityConstraintException::class,
            new NullConstraintException('x')
        );
    }

    public function testParentOfForeignKeyConstraint(): void
    {
        $this->assertInstanceOf(
            IntegrityConstraintException::class,
            new ForeignKeyConstraintException('x')
        );
    }

    public function testParentOfCheckConstraint(): void
    {
        $this->assertInstanceOf(
            IntegrityConstraintException::class,
            new CheckConstraintException('x')
        );
    }

    public function testParentOfDeadlock(): void
    {
        $this->assertInstanceOf(
            DatabaseTransactionRollbackException::class,
            new DatabaseDeadlockException('x')
        );
    }

    public function testParentOfReadOnly(): void
    {
        $this->assertInstanceOf(
            DatabaseTransactionException::class,
            new DatabaseReadOnlyException('x')
        );
    }

    public function testParentOfInvalidTable(): void
    {
        $this->assertInstanceOf(
            DatabaseObjectNotFoundException::class,
            new InvalidTableException('x')
        );
    }

    public function testParentOfEmptyQuery(): void
    {
        $this->assertInstanceOf(
            DatabaseQueryException::class,
            new EmptyQueryException('x')
        );
    }

    public function testParentOfBadValue(): void
    {
        $this->assertInstanceOf(
            DatabaseQueryException::class,
            new BadValueException('x')
        );
    }

    public function testParentOfBadField(): void
    {
        $this->assertInstanceOf(
            DatabaseQueryException::class,
            new BadFieldException('x')
        );
    }

    public function testPreviousExceptionIsPreserved(): void
    {
        $previous = new \RuntimeException('cause');
        $exception = DatabaseErrorMapper::createException(
            'test',
            null,
            DuplicateDatabaseRecordException::MYSQL_ER_DUP_ENTRY,
            $previous
        );

        $this->assertSame($previous, $exception->getPrevious());
    }
}
