<?php

namespace Modules\Docs\Services;

use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsDoc;

class DocsAppMenuService
{
    // 特殊空白符号 https://blog.csdn.net/usenk/article/details/120647093
    private $symbol = [
        'null' => '　', // 注意，左边引号里面不是空格，而是一个特殊的符号
        'middel' => '├',
        'left' => '⠀│ ',
        'last' => '└',
        'node' => '─',
    ];

    /**
     * 文档左侧菜单目录 TREE
     *
     *
     * @return array
     */
    public function tree(mixed $menus, int $level = -1, int $parentHasLastNodeNum = 0)
    {
        $level++;
        $_menus = collect($menus);
        $tree = [];
        $_count = $_menus->count();
        foreach ($_menus as $key => $menu) {
            $isLast = $_count == $key + 1 ? 1 : 0;

            $leftSymbol = $level > 0 ? ($level - $parentHasLastNodeNum - 1 > 0 ? str_repeat($this->symbol['left'], $level - $parentHasLastNodeNum - 1) : '') : '';
            $nullSymbol = $parentHasLastNodeNum > 0 ? str_repeat($this->symbol['null'], $parentHasLastNodeNum) : '';
            // 最后一个节点
            $lastSymbol = $level > 0 ? ($isLast ? $this->symbol['last'] : $this->symbol['middel']) : '';
            $tree[] = [
                'id' => $menu->id,
                'name' => $leftSymbol.$nullSymbol.$lastSymbol.$menu->name,
            ];
            $child = $this->tree($menu->menus, $level, $parentHasLastNodeNum + $isLast);
            ! empty($child) && $tree = array_merge($tree, $child);
        }

        return $tree;
    }

    /**
     * 通过app获取菜单数据
     *
     * @param  bool  $isTipsPage  是否是提示页面,提示页面不需要激活菜单
     * @return string 菜单数据
     */
    public function appMenusString(DocsApp $docsApp, bool $isTipsPage = false): string
    {
        $docsApp->load(['menus']);
        $menus = $docsApp->menus;
        $firstDocId = $isTipsPage ? -1 : $this->getAppFirstDocId($menus);
        $activeMenuIds = $this->getActiveDocParentIds($menus, $firstDocId);

        return $this->leftMenu($docsApp->menus, $activeMenuIds, $firstDocId);
    }

    /**
     * 通过doc获取菜单数据
     */
    public function docMenusString(DocsApp $docsApp, DocsDoc $docsDoc): string
    {
        $docsApp->load(['menus']);
        $menus = $docsApp->menus;
        $activeMenuIds = $this->getActiveDocParentIds($menus, $docsDoc->id);

        return $this->leftMenu($menus, $activeMenuIds, $docsDoc->id);
    }

    /**
     * 获取doc文档的 上一个文档 和 下一个文档
     *      一般只有查看文档详情的时候才会用到
     *
     *
     * @return array
     */
    public function getDocPrevAndNextDoc(DocsApp $docsApp, DocsDoc $docsDoc)
    {
        $docsApp->load(['menus']);
        $menus = $docsApp->menus;
        [$prev, $next] = $this->getAdjacentDoc($menus, $docsDoc->id);

        // return compact('prev', 'next');
        return [$prev, $next];
    }

    // 获取相邻的文档：上一个文档、下一个文档、当前文档
    private function getAdjacentDoc($menus, $docId, $getFirst = false, $isFound = false)
    {
        $lastDoc = []; // 最后一个文档 或者 找到目标文档时的前一个文档
        $prevDoc = []; // 上一个文档
        $nextDoc = []; // 下一个文档

        $menusObj = collect($menus);
        foreach ($menusObj as $key => $menu) {
            if ($this->isDoc($menu)) {
                if ($getFirst) {
                    $currentDoc = $menu->toArray();

                    return [$prevDoc, $currentDoc, $currentDoc, $isFound];
                }
                if ($menu->id == $docId) {
                    $isFound = true; // 找到目标文档
                    if (isset($menusObj[$key - 1])) {
                        $prevDoc = $menusObj[$key - 1];
                    }
                    if (isset($menusObj[$key + 1])) {
                        $nextDoc = $menusObj[$key + 1];
                    }

                    return [$prevDoc ? $prevDoc->toArray() : $prevDoc, $nextDoc ? $nextDoc->toArray() : $nextDoc, $lastDoc, $isFound];
                } else {
                    $lastDoc = $menu->toArray();
                }
            } else {
                $childMenus = collect($menu->menus);
                $childDocs = collect($menu->docs);
                if ($childDocs->isNotEmpty()) {
                    [$prev, $next, $last, $found] = $this->getAdjacentDoc($childDocs, $docId, $getFirst, $isFound);
                    if (empty($prev) && empty($next) && $last) {
                        $lastDoc = $last;
                    }
                    if ($prev) {
                        $prevDoc = $prev;
                    }
                    // 找到文档
                    if ($prev || $found) {
                        $isFound = true;
                        $getFirst = true;
                    }
                    if ($found && empty($prevDoc)) {
                        $prevDoc = $lastDoc;
                    }
                    if ($next) {
                        $nextDoc = $next;
                        break;
                    }
                }
                if ($childMenus->isNotEmpty()) {
                    [$prev, $next, $last, $found] = $this->getAdjacentDoc($childMenus, $docId, $getFirst);
                    if (empty($prev) && empty($next) && $last) {
                        $lastDoc = $last;
                    }
                    if ($prev) {
                        $prevDoc = $prev;
                    }
                    // 找到文档
                    if ($prev || $found) {
                        $isFound = true;
                        $getFirst = true;
                    }
                    if ($found && empty($prevDoc)) {
                        $prevDoc = $lastDoc;
                    }
                    if ($next) {
                        $nextDoc = $next;
                        break;
                    }
                }
            }
        }
        if ($prevDoc && $prevDoc['id'] == $docId) {
            $prevDoc = [];
        }
        if ($nextDoc && $nextDoc['id'] == $docId) {
            $nextDoc = [];
        }

        return [$prevDoc, $nextDoc, $lastDoc, $isFound];
    }

    /**
     * docApp的菜单
     *      li: menu(docs-closed、docs-open) 、 doc(docs-active、无)
     *      ul: (docs-open、无)
     *
     * @param  array  $activeMenuIds  当前激活的菜单id
     * @param  int  $activeDocId  当前激活的文档id
     */
    private function leftMenu(mixed $menus, array $activeMenuIds = [], int $activeDocId = 0): string
    {
        $str = '';

        $menus = collect($menus);
        if ($menus->isEmpty()) {
            return $str;
        }
        // 判断是否为移动端
        $isMobile = is_mobile();
        $isLogin = auth()->check();
        foreach ($menus as $item) {
            $isMenu = ! $this->isDoc($item);
            $href = $isMenu ? 'javascript:;' : route('docs.doc.show', ['docsApp' => $item->doc_app_id, 'docsDoc' => $item->id]);

            $liClass = $isMenu ? (in_array($item->id, $activeMenuIds) ? 'docs-open docs-active fix-docs-active' : 'docs-closed') : ($item->id == $activeDocId ? 'docs-active fix-docs-active' : '');

            $rightMenuClass = $isMenu ? ' menu-dir' : ' menu-node';
            // docs-closed 、 docs-open 、 docs-active、fix-docs-active(固定页面激活状态,避免页面移除docs-active样式，和docs-active效果一样)
            if ($isLogin) {
                $str .= '<li class="'.$liClass.$rightMenuClass.'" data-id="'.$item->id.'"  data-name="'.($isMenu ? $item->name : '').'">';
                if ($isMobile) {
                    $str .= '<a href="'.$href.'">'.($isMenu ? $item->name : $item->title).'</a>';
                } else {
                    $str .= '<a href="'.$href.'" data-tips title="鼠标右键操作">'.($isMenu ? $item->name : $item->title).'</a>';
                }
            } else {
                $str .= '<li class="'.$liClass.$rightMenuClass.'">';
                $str .= '<a href="'.$href.'">'.($isMenu ? $item->name : $item->title).'</a>';
            }

            $childMenus = collect($item->menus);
            $childDocs = collect($item->docs);
            if ($childMenus->isNotEmpty() || $childDocs->isNotEmpty()) {
                // docs-open
                $ulClass = in_array($item->id, $activeMenuIds) ? 'docs-open' : '';

                $str .= '<ul class="'.$ulClass.'">';
                $str .= $this->leftMenu($childDocs, $activeMenuIds, $activeDocId);
                $str .= $this->leftMenu($childMenus, $activeMenuIds, $activeDocId);
                $str .= '</ul>';
            }
            $str .= '</li>';
        }

        return $str;
    }

    /**
     * 获取当前激活文档的所有父级菜单id
     *      如果$docId为空，则获取当前应用的第一个文档id
     *
     * @param  int  $docId  当前激活的文档id,
     *                      0/空 : 获取当前应用的第一个文档id,
     *                      -1   : 提示页面不需要激活菜单
     */
    public function getActiveDocParentIds(mixed $menus, int $docId = 0): array
    {
        $parentIds = [];
        if ($docId == -1) {
            return $parentIds;
        }
        $list = collect($menus);
        if ($list->isEmpty()) {
            return $parentIds;
        }
        if (empty($docId)) {
            $docId = $this->getAppFirstDocId($menus);
        }
        foreach ($list as $item) {
            if ($item->id == $docId) {
                $parentIds[] = $item->id;
                break;
            }
            $childMenus = collect($item->menus);
            $childDocs = collect($item->docs);
            if ($childDocs->isNotEmpty()) {
                // 此文章id 在此目录下
                if (in_array($docId, $childDocs->pluck('id')->toArray())) {
                    return [$item->id];
                }
            }

            if ($childMenus->isNotEmpty()) {
                // 再往下找子目录里面有没有此文章id
                if (! empty($menusIds = $this->getActiveDocParentIds($childMenus, $docId))) {
                    $parentIds = array_merge([$item->id], $menusIds);
                }
            }
        }

        return ! empty($parentIds) ? array_unique($parentIds) : $parentIds;
    }

    /**
     * 获取当前应用的第一个文档id
     */
    public function getAppFirstDocId(mixed $menus): int
    {
        $list = collect($menus);
        if ($list->isEmpty()) {
            return 0;
        }
        foreach ($list as $item) {
            $childMenus = collect($item->menus);
            $childDocs = collect($item->docs);
            if ($childMenus->isNotEmpty() || $childDocs->isNotEmpty()) {
                if (! empty($menusIds = $this->getAppFirstDocId($childMenus))) {
                    return $menusIds;
                }
                if (! empty($docsIds = $this->getAppFirstDocId($childDocs))) {
                    return $docsIds;
                }
            }
            if ($this->isDoc($item)) {
                return $item->id;
            }
        }

        return 0;
    }

    // 区分菜单(menu)和文档(doc)
    private function isDoc(mixed $item): bool
    {
        return isset($item['doc_menu_id']);
    }
}
