<extend name="layouts:master" />
<block name="link"></block>
<block name="src">
<script>
var thisPageThings = {};
//操作所需的url
thisPageThings.datagridUrl = "{:$url['datagridUrl']}";
thisPageThings.addUrl = "{:$url['addUrl']}";
thisPageThings.editUrl = "{:$url['editUrl']}";
thisPageThings.removeUrl = "{:$url['removeUrl']}";
thisPageThings.checkClick = true;
//基本的搜索
thisPageThings.show = function(){
    $('#searchForm').form('reset');
    $('#datagrid').datagrid('load',{
        rand:Math.random()
    });
}
//接收数据之后的操作
thisPageThings.callback = function(data){
   // data = eval('('+data+')');
    $.messager.alert('操作提示',data.message,'info');
    $('#datagrid').datagrid('load',{
        rand:Math.random()
    });
}
//打开增加dialog
thisPageThings.addBar = function(){
	$('#addDialog').dialog('open');
}
//打开编辑dialog
thisPageThings.editBar = function(){
    var infos = $('#datagrid').datagrid('getSelections');
    if(infos.length > 1){
        $.messager.alert('操作提示','请选择单个进行操作','info');
        return false;
    }
    if(infos.length == 1){
        $('#editForm').form('load',infos[0]);
        $('#editDialog').dialog('open');
    }
}
//提交增加
thisPageThings.add = function(){
    if(thisPageThings.checkClick){
        thisPageThings.checkClick = false;
        $('#addForm').form('submit',{
            url:thisPageThings.addUrl,
            success:function(data){
                thisPageThings.checkClick = true;
                data = eval('('+data+')');
                $('#addDialog').dialog('close');
                $.messager.alert('操作提示',data.message,'info');
                $('#datagrid').datagrid('load',{
                    rand:Math.random()
                });
            }
        });
    }
}
//提交编辑
thisPageThings.edit = function(){
    $('#editForm').form('submit',{
        url:thisPageThings.editUrl,
        success:function(data){
            data = eval('('+data+')');
            $('#editDialog').dialog('close');
            $.messager.alert('操作提示',data.message,'info');
            $('#datagrid').datagrid('load',{
                rand:Math.random()
            });
        }
    });
}
//删除事件
thisPageThings.remove = function(){
    var infos = $('#datagrid').datagrid('getSelections');
    var ids = [];
    if(infos.length == 0)
        return false;

    $.each(infos,function(n,m){
        var id= m.{table_id};
        ids.push(id);
    });
    ids = ids.join(',');
    $.ajax({
        url:thisPageThings.removeUrl,
        type:'post',
        data:{
            {table_id}:ids
        },
        success:function(data){
            thisPageThings.callback(data);
        }
    });
}
//搜索事件
thisPageThings.search = function(){
{js_search}
    $('#datagrid').datagrid('load',{
{js_search_data}
    });
}
{function}
//初始化表格
$(function(){
	$('#datagrid').datagrid({
        url:thisPageThings.datagridUrl,
        method:'get',
        queryParams:{
            rand:Math.random()
        },
        title:'列表',
        fitColumns:true,
        rownumbers:true,
        fit:true,
        pageSize:15,
        pageNumber:1,
        pageList:[10,15,20,25,30,40,50],
        columns:[[
{fields}
        ]],
        pagination:true
    });
});
</script>
</block>
<block name="main">
	<table id="datagrid" toolbar="#toolbar"></table>

    <div id="toolbar">
        <form id="searchForm" method="post" style="margin-top: 3px;" novalidate>
             <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon icon-filter" plain="true" onclick="thisPageThings.show()">全部</a>&nbsp;&nbsp;&nbsp;
            {search}
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="thisPageThings.search()">搜索</a>
        </form>
        <a href="javascript:void(0)" onclick="thisPageThings.addBar()" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true">添加</a>
        <a href="javascript:void(0)" onclick="thisPageThings.editBar()" class="easyui-linkbutton" data-options="iconCls:'icon icon-edit',plain:true">修改</a>
        <a href="javascript:void(0)" onclick="thisPageThings.remove()" class="easyui-linkbutton" data-options="iconCls:'icon icon-delete',plain:true">
        删除</a>
    </div>
<!-- 增加 -->
    <div id="addDialog" class="easyui-dialog" title="添加" buttons="#addButtons"
        data-options="iconCls:'icon-add',resizable:true,modal:true,closed:true">
    	<form id="addForm" method="post">
{col}
    	</form>
    </div>
    <div id="addButtons">
    	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="thisPageThings.add()" style="width:90px">确认</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:
                $('#addDialog').dialog('close')" style="width:90px">取消</a>
    </div>
<!-- 修改 -->
    <div id="editDialog" class="easyui-dialog" title="修改" buttons="#editButtons"
        data-options="iconCls:'icon-add',resizable:true,modal:true,closed:true">
        <form id="editForm" method="post">
{col}
        </form>
    </div>
    <div id="editButtons">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="thisPageThings.edit()" style="width:90px">确认</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:
                $('#editDialog').dialog('close')" style="width:90px">取消</a>
    </div>

</block>