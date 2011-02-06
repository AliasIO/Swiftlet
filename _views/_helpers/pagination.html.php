<?php

echo '<p class="pagination">';

$url = $this->route(preg_replace('/\/' . preg_quote($params['name']) . '\-page\-[0-9]+/', '', $this->request) . '/' . $this->h($params['name']) . '-page-');

echo '<span class="pagination-goto">' . $this->t('Go to page') . ':</span class="pagination-goto"> ';

if ( $params['page'] > 1 )
{
	echo '<a class="pagination-previous" href="' . $url . ( $params['page'] - 1 ) . '#' . $params['name'] . '">' . $this->t('previous') . '</a> ';
}

for ( $i = 1; $i <= 3; $i ++ )
{
	if ( $i > $params['pages'] )
	{
		break;
	}

	echo ( $i == $params['page'] ? $i : '<a class="pagination-page" href="' . $url . $i . '#' . $params['name'] . '">' . $i . '</a>' ) . ' ';
}

if ( $params['page'] > 7 )
{
	echo '<span class="pagination-break">&hellip;</span> ';
}

for ( $i = $params['page'] - 3; $i <= $params['page'] + 3; $i ++ )
{
	if ( $i > 3 && $i < $params['pages'] - 2 )
	{
		echo ( $i == $params['page'] ? $i : '<a class="pagination-page" href="' . $url . $i . '#' . $params['name'] . '">' . $i . '</a>' ) . ' ';
	}
}

if ( $params['page'] < $params['pages'] - 6 )
{
	echo '<span class="pagination-break">&hellip;</span> ';
}

if ( $params['pages'] > 3 )
{
	for ( $i = $params['pages'] - 2; $i <= $params['pages']; $i ++)
	{
		if ( $i > 3 )
		{
			echo ( $i == $params['page'] ? $i : '<a class="pagination-page" href="' . $url . $i . '#' . $params['name'] . '">' . $i . '</a>' ) . ' ';
		}
	}
}

if ( $params['page'] < $params['pages'] )
{
	echo '<a class="pagination-next" href="' . $url . ( $params['page'] + 1 ) . '#' . $params['name'] . '">' . $this->t('next') . '</a> ';
}

echo '</p>';
