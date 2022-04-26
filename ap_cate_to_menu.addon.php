<?php
if ( !defined('RX_VERSION') ) return;
if ( $called_position !== 'after_module_proc' ) return;

getController('module')->addTriggerFunction('layout', 'before', function($oModule) use($addon_info)
{
	// 애드온에 대상 메뉴 설정이 안 돼 있으면 애드온 중지
	$target_menu = Context::get($addon_info->target_menu);
	if ( !$target_menu ) return;

	// 레이아웃에 메뉴 지정이 안 돼 있으면 애드온 중지
	if ( !Context::get('layout_info')->menu_count ) return;

	// 카테고리 정보가 세팅되어 있지 않으면 모듈번호를 통해 가져오고 그래도 없으면 애드온 중지
	$category_list = Context::get('category_list');
	if ( !$category_list )
	{
		// 현재의 모듈 번호 추출
		$category_list = getModel('document')->getCategoryList($oModule->module_srl);
		if ( !$category_list ) return;
	}

	// 카테고리 번호 없이 문서 읽기 모드일 때, 카테고리 번호를 Context 안에 설정
	if ( !Context::get('category') && Context::get('document_srl') )
	{
		Context::set('category', Context::get('oDocument')->variables->category_srl);
	}
	$category = Context::get('category');

	// 카테고리 목록을 메뉴 목록 형태로 변환
	$target_menu_list = array();
	foreach ( $category_list as $key => $val )
	{
		if ( $val->depth || !$val->grant ) continue;
		$target_menu_list[$key] = array();
		$target_menu_list[$key]['node_srl'] = $val->category_srl;
		$target_menu_list[$key]['parent_srl'] = $val->parent_srl;
		$target_menu_list[$key]['menu_name_key'] = $val->title;
		$target_menu_list[$key]['isShow'] = 1;
		$target_menu_list[$key]['text'] = $val->text;
		$target_menu_list[$key]['href'] = getUrl('', 'mid', Context::get('mid'), 'category', $val->category_srl);
		$target_menu_list[$key]['url'] = getUrl('', 'mid', Context::get('mid'), 'category', $val->category_srl);
		$target_menu_list[$key]['is_shortcut'] = 'N';
		$target_menu_list[$key]['desc'] = $val->description;
		$target_menu_list[$key]['open_window'] = 'N';
		$target_menu_list[$key]['normal_btn'] = '';
		$target_menu_list[$key]['hover_btn'] = '';
		$target_menu_list[$key]['active_btn'] = '';
		$target_menu_list[$key]['selected'] = ($category == $val->category_srl || in_array($category, $val->childs)) ? 1 : 0;
		$target_menu_list[$key]['expand'] = $val->expand ? 'Y' : 'N';
		$target_menu_list[$key]['list'] = array();
		$target_menu_list[$key]['link'] = $val->text;
		$target_menu_list[$key]['depth'] = $val->depth;
		if ( $val->child_count )
		{
			$child_list = array();
			foreach ( $val->childs as $k => $v )
			{
				$val2 = $category_list[$v];
				if ( $val2->depth > 1 || !$val2->grant ) continue;
				$child_list[$v] = array();
				$child_list[$v]['node_srl'] = $val2->category_srl;
				$child_list[$v]['parent_srl'] = $val2->parent_srl;
				$child_list[$v]['menu_name_key'] = $val2->title;
				$child_list[$v]['isShow'] = 1;
				$child_list[$v]['text'] = $val2->text;
				$child_list[$v]['href'] = getUrl('', 'mid', Context::get('mid'), 'category', $val2->category_srl);
				$child_list[$v]['url'] = getUrl('', 'mid', Context::get('mid'), 'category', $val2->category_srl);
				$child_list[$v]['is_shortcut'] = 'N';
				$child_list[$v]['desc'] = $val2->description;
				$child_list[$v]['open_window'] = 'N';
				$child_list[$v]['normal_btn'] = '';
				$child_list[$v]['hover_btn'] = '';
				$child_list[$v]['active_btn'] = '';
				$child_list[$v]['selected'] = ($category == $val2->category_srl) ? 1 : 0;
				$child_list[$v]['expand'] = $val2->expand ? 'Y' : 'N';
				$child_list[$v]['list'] = array();
				$child_list[$v]['link'] = $val2->text;
				$child_list[$v]['depth'] = $val2->depth;
			}
			$target_menu_list[$key]['list'] = $child_list;
		}
	}

	// 메뉴 목록을 카테고리 목록으로 대체
	$_which = $addon_info->into_which;
	if ( !$_which ) $target_menu->list = $target_menu_list;
	else if ( $_which === 'C' ) $target_menu->list = array_merge($target_menu->list, $target_menu_list);
	else if ( $_which === 'N' && $addon_info->node_srl )
	{
		$_node_srl = (int)$addon_info->node_srl;
		foreach ( $target_menu->list as $key => $val )
		{
			if ( $key === $_node_srl )
			{
				$target_menu->list[$key]['list'] = $target_menu_list;
				break;
			}
			if ( count($val['list']) )
			{
				foreach ( $val['list'] as $k => $v )
				{
					if ( $k === $_node_srl )
					{
						$target_menu->list[$key]['list'][$k]['list'] = $target_menu_list;
						break;
					}
				}
			}
		}
	}
	Context::set($addon_info->target_menu, $target_menu);
});

?>