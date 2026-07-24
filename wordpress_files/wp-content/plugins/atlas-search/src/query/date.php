<?php

namespace AtlasSearch\Query\Date;

/**
 * This function returns a string filter that represents the date_query.
 *
 * $date_query_args = array(
 *      'relation' => 'OR',
 *      array(
 *          'year'          => 2021,
 *          'month'         => 1,
 *          'day'           => 1,
 *          'compare'       => '=',
 *      ),
 *      array(
 *          'before'        => '2024-01-13',
 *          'inclusive'     => true,
 *      ),
 *      array(
 *          'year'          => 2023,
 *          'month'         => 12,
 *          'day'           => 21,
 *          'hour'          => 12,
 *          'minute'        => 12,
 *          'second'        => 12,
 *          'compare'       => 'NOT IN',
 *      ),
 * );
 * $date_query = new WP_Date_Query( $date_query_args );
 *
 * @param \WP_Query $wp_query WP Query.
 *
 * @return string|null
 */
function get_date_query_filter( \WP_Query $wp_query = null ) {
	if ( ! isset( $wp_query ) ) {
		return null;
	}

	if ( ! isset( $wp_query->date_query ) ) {
		return null;
	}

	if ( empty( $wp_query->date_query->queries ) ) {
		return null;
	}

	if ( empty( $wp_query->date_query->queries ) ) {
		return null;
	}

	return date_query_filter_recursive( $wp_query->date_query->queries );
}

function date_query_filter_recursive( array $wp_date_queries ) {
	$queries              = [];
	$persistent_inclusive = true;
	$relation             = $wp_date_queries['relation'] ?? 'AND';
	$before               = $wp_date_queries['before'] ?? null;
	$after                = $wp_date_queries['after'] ?? null;

	foreach ( $wp_date_queries as $key => $query ) {
		if ( 'relation' === $key ) {
			continue;
		}

		$year      = $query['year'] ?? null;
		$month     = $query['month'] ?? null;
		$day       = $query['day'] ?? null;
		$hour      = $query['hour'] ?? null;
		$minute    = $query['minute'] ?? null;
		$second    = $query['second'] ?? null;
		$inclusive = $query['inclusive'] ?? true;

		if ( false === $inclusive ) {
			$persistent_inclusive = false;
		}

		if ( isset( $query['before'] ) && isset( $query['after'] ) ) {
			$before = $query['before'] ?? null;
			$after  = $query['after'] ?? null;
		}

		if (
			isset( $year )
			|| isset( $month )
			|| isset( $day )
			|| isset( $hour )
			|| isset( $minute )
			|| isset( $second )
			|| isset( $query['before'] )
			|| isset( $query['after'] )
		) {
			$queries[] = generate_simple_query( $query, $before, $after, $persistent_inclusive );
		} elseif ( is_array( $query ) && ! isset( $before ) && ! isset( $after ) ) {
			$recursive_result = date_query_filter_recursive( $query );

			if ( '' !== $recursive_result ) {
				$queries[] = $recursive_result;
			}
		} else {
			continue;
		}
	}

	$queries = array_unique( $queries );

	if ( empty( $queries ) ) {
		return '';
	}

	$non_empty_queries = [];

	foreach ( $queries as $q_str ) {
		if ( is_string( $q_str ) && '' !== $q_str ) {
			$non_empty_queries[] = $q_str;
		}
	}

	return '(' . implode(
		' ' . $relation . ' ',
		array_map(
			static function ( $v ) use ( $relation ) {
				if ( 'OR' === $relation && false !== strpos( $v, 'AND' ) ) {
					return '(' . $v . ')';
				}

				return $v;
			},
			$non_empty_queries
		)
	) . ')';
}

function normalize_datetime_string( $date_string ) {
	if ( ! \is_string( $date_string ) ) {
		return $date_string;
	}

	// Trim whitespace that might cause issues.
	$date_string = trim( $date_string );

	// Convert MySQL datetime format to ISO 8601 format for Elasticsearch.
	// Matches: "2026-06-01 14:30:00" (MySQL format with space separator)
	// Replaces the space with "T": "2026-06-01T14:30:00" (ISO 8601 format)
	// Date-only strings like "2026-06-01" pass through unchanged (no match).
	return preg_replace( '/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/', '$1T$2', $date_string );
}

function generate_simple_query( $query, $before, $after, $persistent_inclusive ) {
	$relation  = $query['relation'] ?? 'AND';
	$compare   = $query['compare'] ?? '=';
	$column    = $query['column'] ?? 'post_date';
	$year      = $query['year'] ?? null;
	$month     = $query['month'] ?? null;
	$day       = $query['day'] ?? null;
	$hour      = $query['hour'] ?? null;
	$minute    = $query['minute'] ?? null;
	$second    = $query['second'] ?? null;
	$before    = $query['before'] ?? $before;
	$after     = $query['after'] ?? $after;
	$inclusive = $persistent_inclusive;

	// Normalize strings early and coerce empty to null to prevent malformed queries.
	if ( is_string( $before ) ) {
		$before = normalize_datetime_string( $before );
		$before = '' === $before ? null : $before;
	}

	if ( is_string( $after ) ) {
		$after = normalize_datetime_string( $after );
		$after = '' === $after ? null : $after;
	}

	$type = 'DATE';

	if ( isset( $hour ) && isset( $minute ) && isset( $second ) ) {
		$type = 'DATETIME';
	}

	$date_format = '%04d-%02d-%02d';

	if ( 'DATETIME' === $type ) {
		$date_string = sprintf( '%04d-%02d-%02dT%02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second );
	} else {
		$date_string = sprintf( $date_format, $year, $month, $day );
	}

	$opening_bracket = $inclusive ? '[' : '{';
	$closing_bracket = $inclusive ? ']' : '}';

	if ( ! empty( $before ) && ! empty( $after ) && ! isset( $hour ) ) {
		$after_date  = is_array( $after ) ? sprintf(
			$date_format,
			$after['year'],
			$after['month'],
			$after['day']
		) : $after;
		$before_date = is_array( $before ) ? sprintf(
			$date_format,
			$before['year'],
			$before['month'],
			$before['day']
		) : $before;
		$range_query = "$column:$opening_bracket$after_date TO $before_date$closing_bracket";
		$inner_query = 'OR' === $relation ? "($range_query)" : $range_query;
	} elseif ( ! empty( $before ) && ! isset( $hour ) ) {
		$before_date = is_array( $before ) ? sprintf(
			$date_format,
			$before['year'],
			$before['month'],
			$before['day']
		) : $before;
		$range_query = "$column:[* TO $before_date$closing_bracket";
		$inner_query = 'OR' === $relation ? "($range_query)" : $range_query;
	} elseif ( ! empty( $after ) && ! isset( $hour ) ) {
		$after_date  = is_array( $after ) ? sprintf(
			$date_format,
			$after['year'],
			$after['month'],
			$after['day']
		) : $after;
		$range_query = "$column:$opening_bracket$after_date TO *]";
		$inner_query = 'OR' === $relation ? "($range_query)" : $range_query;
	} else {
		$inner_query = 'OR' === $relation ? '(' . $column . inner_operator(
			$date_string,
			$compare
		) . ')' : $column . inner_operator( $date_string, $compare );
	}

	return outer_operator( $inner_query, $compare );
}

function outer_operator( string $query_string, ?string $operator = '=' ) {
	switch ( $operator ) {
		case '!=':
			return 'NOT ' . $query_string;
		case '=':
		default:
			return $query_string;
	}
}

function inner_operator( string $query_string, ?string $operator = '=' ) {
	switch ( $operator ) {
		case '>':
			return ':{' . $query_string . ' TO *]';
		case '<':
			return ':[* TO ' . $query_string . '}';
		case '<=':
			return ':[* TO ' . $query_string . ']';
		case '>=':
			return ':[' . $query_string . ' TO *]';
		case '=':
		default:
			return ':"' . $query_string . '"';
	}
}
