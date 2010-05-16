<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../',
	'pageTitle' => 'Documenation'
	);

require($contrSetup['rootPath'] . '_model/init.php');

if ( isset($model->routeParts[1]) )
{
	$pageId = $model->routeParts[1];
}

$pages    = array();
$pagesNum = array();
$overview = make_overview(make_tree('./'));

/*
 * Go to the first page by default
 */
if ( !$pageId )
{
	header('Location: ./' . $pagesNum[0]['id']);
	
	$model->end();
}

if ( $pageId && isset($pages[$pageId]) )
{
	/*
	 * Generate Previous, Next and Up links
	 */
	foreach ( $pagesNum as $i => $v )
	{
		if ( $v['id'] == $pageId )
		{
			$pagePrev = isset($pagesNum[$i - 1]) ? '<a href="./' . $pagesNum[$i - 1]['id'] . '">&lsaquo; ' . $pagesNum[$i - 1]['number'] . ' ' . $pagesNum[$i - 1]['title'] . '</a>' : '';
			$pageNext = isset($pagesNum[$i + 1]) ? '<a href="./' . $pagesNum[$i + 1]['id'] . '">' . $pagesNum[$i + 1]['number'] . ' ' . $pagesNum[$i + 1]['title'] . ' &rsaquo;</a>' : '';
			
			break;
		}
	}
	
	$pageParentId = $pages[$pageId]['parent_id'];
	
	$pageUp = $pageParentId ? '<a href="./' . $pageParentId . '">' . $pages[$pageParentId]['number'] . ' ' . $pages[$pageParentId]['title'] . '</a>' : '';

	/*
	 * Show an overview of pages if the page is actually a directory
	 */
	$pageTitle = $pages[$pageId]['number'] . ' ' . $pages[$pageId]['title'];

	$pageContents =
		isset($pages[$pageId]['branch']) ?
			'<h2>' . $pages[$pageId]['title'] . '</h2>' . make_overview($pages[$pageId]['branch']) :
			file_get_contents($pages[$pageId]['url'])
			;
	
	$pageContents = preg_replace('/<h2>/', '<h2>' . $pages[$pageId]['number'] . ' ', $pageContents);

	/*
	 * Detect internal links
	 */
	preg_match_all('/href="\?(.+?)(#.+)?"/i', $pageContents, $m);
	
	if ( $m )
	{
		foreach ( $m[0] as $i => $v )
		{
			$pageContents = str_replace($v, 'class="' . ( isset($pages[$m[1][$i]]) ? 'ref' : 'ref_new' ) . '" href="./' . $m[1][$i] . $m[2][$i] . '"', $pageContents);
		}
	}

	/*
	 * Add links to headings
	 */
	/*
	preg_match_all('/<h([2-6])>(.+?)<\/h\\1>/i', $pageContents, $m);

	if ( $m )
	{
		foreach ( $m[0] as $i => $v )
		{
			$anchor = make_id($m[2][$i]);
			
			$pageContents = str_replace($v, '<h' . $m[1][$i] . '>' . $m[2][$i] . ' <a id="' . $anchor . '" class="anchor" href="#' . $anchor . '">#</a></h' . $m[1][$i] . '>', $pageContents);
		}
	}
	*/

	/*
	 * Code syntax markup
	 */
	preg_match_all('/<pre>(.+?)<\/pre>/s', $pageContents, $m);

	if ( $m )
	{
		foreach ( $m[0] as $v )
		{
			$code = highlight_string(preg_replace('/<\/?pre>\r*\n*/', '', $v), TRUE);
			
			$gutter = '';
			
			for ( $i = 1; $i <= ( $lines = substr_count($code, '<br />') ); $i ++ )
			{
				$gutter .= sprintf('%0' . strlen($lines) . 'd', $i) . '<br/>';
			}

			$code = '<div class="syntax"><div class="gutter">' . $gutter . '</div><div class="code">' . $code . '</div></div>';
			
			$pageContents = str_replace($v, $code, $pageContents);
		}
	}
}

/*
 * Make tree
 * @param string $dir
 * @param int $parentNum
 * @return array
 */
function make_tree($dir, $parentNum = 0, $parentId = '')
{
	$num = $parentNum ? 0 : 1;

	$files    = array();
	$dirFiles = array();

	if ( $dir_handle = opendir($dir) )
	{
		while ( ( $file = readdir($dir_handle) ) !== FALSE )
		{
			$dirFiles[strtolower($file)] = $file;
		}

		ksort($dirFiles);

		foreach ( $dirFiles as $file )
		{
			if ( is_file($dir . $file) && preg_match('/\.html$/', $file) )
			{
				$id = make_id($file);
				
				$file_handle = fopen($dir . $file, 'r');

				$title = '';
				
				while ( !feof($file_handle) && !$title )
				{
					preg_match('/<h2>(.+)<\/h2>/', fgets($file_handle), $m);
					
					if ( $m ) $title = $m[1];
				}

				fclose($file_handle);
				
				$files[$dir . $file] = array(
					'id'        => $id,
					'parent_id' => $parentId,
					'url'       => $dir . $file,
					'number'    => ( $parentNum ? $parentNum . '.' : '' ) . $num,
					'title'     => $title
					);
				
				$num ++;
			}
			elseif ( is_dir($dir . $file) && $file != '.' && $file != '..' )
			{			
				$id = make_id($file);
				
				$files[$dir . $file] = array(
					'id'        => $id,
					'parent_id' => $parentId,
					'url'       => $dir . $file,
					'number'    => ( $parentNum ? $parentNum . '.' : '' ) . $num,
					'title'     => $file,
					'branch'    => make_tree($dir . $file . '/', ( $parentNum ? $parentNum . '.' : '' ) . $num, $id)
					);
				
				$num ++;
			}
		}

		closedir($dir_handle);
	}
	
	//asort($files);

	return $files;
}

/*
 * Make overview
 * @param array $tree
 * @return array
 */
function make_overview($tree)
{
	global $pageId, $pages, $pagesNum;

	$overview = '<ul>';
	
	foreach ( $tree as $url => $branch )
	{
		$pages[$branch['id']] = $branch;
		$pagesNum[]           = $branch;
		
		$hasBranch = isset($branch['branch']);
		
		$overview .= '
			<li>
				<span>
					' . ( $pageId == $branch['id'] ? '' : '<a href="./' . $branch['id'] . '">' ) . '
					' . $branch['number'] . ' ' . $branch['title'] . '
					' . ( $pageId == $branch['id'] ? '' : '</a>' ) . '
				</span>
				' . ( $hasBranch ? make_overview($branch['branch']) : '' ) . '
			</li>
			';
	}

	$overview .= '</ul>';
	
	return $overview;
}

/*
 * Make ID
 * @param string $file
 */
function make_id($file)
{
	return trim(preg_replace('/__+/', '_', preg_replace('/[^a-z0-9]/', '_', strtolower(preg_replace('/\.html$/', '', $file)))), '_');
}

$view->overview     = $overview;
$view->pageContents = $pageContents;
$view->pagesNum     = $pagesNum;
$view->pagePrev     = $pagePrev;
$view->pageUp       = $pageUp;
$view->pageNext     = $pageNext;

$model->view->load('docs.html.php');

$model->end();
