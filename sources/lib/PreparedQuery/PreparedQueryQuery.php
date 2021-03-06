<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\PreparedQuery;

use PommProject\Foundation\Query\QueryInterface;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\ConvertedResultIterator;

/**
 * PreparedQueryQuery
 *
 * Query using the prepared_statement client.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientInterface
 */
class PreparedQueryQuery extends Client implements QueryInterface
{
    /**
     * query
     *
     * @see QueryInterface
     */
    public function query($sql, array $parameters = [])
    {
        $resource = $this
            ->getSession()
            ->getClientUsingPooler('prepared_query', $sql)
            ->execute($parameters)
            ;

        return new ConvertedResultIterator(
            $resource,
            $this->getSession()
        );
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'query';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return get_class($this);
    }
}
