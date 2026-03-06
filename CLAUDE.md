## Code style:
use PHP 8.5, declare(strict_types=1);, PSR-12, max 140 chars/line.
PHP 8.5 supports pipe operator and clone($object, [])
Short arrays [], no Yoda-Conditions, no Assignment-in-Condition.
prefer final readonly and immutability
replace Primitive by Value Objects (Id, Price, DateRange, Status).
DTO/Resource are flat
keep methods short, use Guard-Clauses, no if-nesting
Domain throws Domain-Exceptions
TypeScript strict style, prefer TypeScript over JavaScript
use readable and speaking variable names, i.e. index instead of i, value instead of v

## Project
use hexagonal design
Infrastructure implements Ports and encapsulates WordPress, DB, Mail, Cron.
Domain knows only businesslogic; wordpress and db only in infrastructure and presentation
Application manages UseCases. DTOs transfer information from usecase to presentation and back