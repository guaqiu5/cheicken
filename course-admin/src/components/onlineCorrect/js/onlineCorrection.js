/**
 * 《在线批改 JavaScript 主框架代码》 'onlineCorrection.js'
 * 
 * 【实现思路】
 * ## 1. 利用开源工具 html2canvas 对指定页面元素截图，生成 canvas 画布，在其上编辑绘制
 * ## 2. 将需要批改的内容装载到指定 div 中，供 html2canvas 截图
 * 
 * 【实现功能】
 * ## 页面首次加载需调用功能 ##
 * --初始化功能：[外部触发] 提供接口 myCanvasManagerInit 外部调用
 * --批改快照加载：[外部触发] 外部数据源数据 向提供接口 draw_html_edited_load 传参
 * ## 页面加载完可调用功能 ##
 * --按钮【批改】：[外部实现] 手动触发批改模块加载，提供接口 draw_correct_btn_click 外部调用，传入必要初始化参数
 * --按钮【保存】：[外部实现] 保存当前编辑快照，提供接口 getCanvasDataUrl 获取 base64 格式图片，外部自定义保存方式
 * --按钮【导出】：[内部实现] 导出单前编辑快照到本地（'.png'格式）
 * --按钮【后退】：[内部实现] 撤销当前绘制操作，退回到前一个快照
 * --按钮【前进】：[内部实现] 反撤销当前绘制操作，前进到后一个快照
 * --按钮【恢复】：[内部实现] 恢复到未编辑状态时的快照
 * --画笔功能：[内部实现] 点击【批改】后鼠标默认为画笔
 * --文字批注功能：[内部实现] 鼠标在编辑区双击，弹出输入框，输入完在编辑框外点击，即批注成功
 * 
 * 【页面主框架】：外部页面使用时需添加这一段 HTML 代码
 *	 	<div class="draw_frame"><!-- 画图主框架区域 -->
 *			<!-- canvas 画图绘制区域 【图层：最上层】【动态生成】 -->
 *			<div class="draw_html_origin"><!-- 原始文本区域 【图层：最底层】【截图来源】-->
 *				<div v-html="item.content" style="padding-left: 10px"></div>
 *			</div><!-- 原始文本区域 end -->
 *			<div class="draw_html_edited"><!-- 数据库编辑快照 【图层：中间层】【仅显示】-->
 *				<img v-if="item.mark" :src="item.mark" @load="draw_html_edited_load($event);"/><!-- 宽高 适应原始文本区域宽高 -->
 *			</div><!-- 数据库编辑快照 end -->
 *			<div class="draw_control"><!-- 画图控制区域 -->
 *				<div class="draw_operate"><!-- 画图操作区域 -->
 *					<button id="draw_correct_btn" class="draw_color_btn" @click="draw_correct_btn_click($event, index, item.id, item.content);">批改</button>
 *					<button id="draw_save_btn" class="draw_color_btn" @click="draw_save_btn_click($event, index, item.id);">保存</button>
 *					<button id="draw_export_btn" class="draw_color_btn">导出</button>
 *					<button id="draw_back_btn" class="draw_color_btn">后退</button>
 *					<button id="draw_forward_btn" class="draw_color_btn">前进</button>
 *					<button id="draw_restore_btn" class="draw_color_btn">恢复</button>
 *				</div><!-- 画图操作区域 end -->
 *			</div><!-- 画图控制区域 end -->
 *		</div><!-- 画图主框架区域 end -->
 * 
 * 【JS外部引用库】
 * https://code.jquery.com/jquery-3.4.1.min.js
 * https://cdn.bootcss.com/html2canvas/0.5.0-beta4/html2canvas.js -- 不支持分辨率参数（改用下面的源码链接）
 * https://cdn.jsdelivr.net/bluebird/latest/bluebird.js *** 用于解决 html2canvas.js 中报 Promise 未定义错误的问题
 *
 * html2canvas 源码地址：https://github.com/eKoopmans/html2canvas/tree/develop/dist/html2canvas.min.js -- 支持分辨率参数
 * 
 * 【出现的问题】
 * ## 1. 解决 html2canvas 截图模糊的问题：https://www.jianshu.com/p/f400634646d6
 * ## 2. 解决 canvas 模糊问题：https://segmentfault.com/a/1190000016819776
 * ## 3. 解决 html2canvas 截图不完整的问题：
 * 			1. 设置待截图元素 div 的固定宽高，防止溢出 div 的部分截取不到
 * 			2. 待截图元素 div 必须在浏览器得到渲染，否则无法截取
 * 			3. 待截图元素 div 的顶边 top 必须在 window 的 top 之下，否则 window.top 之上的 div 部分丢失
 * 			4. 待截图元素 div 的其它边 left bottom right，可以在 window 之外
 * 	  	a.如果出现【问题3】，则可以设置元素所在页面的滚动条滚动位置，使截图时保证元素满足【问题3】条件
 * 	  	b.网上还有其它方法：
 * 			1. 如将 待截图元素 div 克隆后追加到 body 后，但存在图片渲染延迟的问题，使截图图片区域空白
 * 			2. 等等....
 * 
 * 【图片 base64 的字符串格式】
 * var imgDataUrl = canvas.toDataURL('image/png');
 * data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlwAAAa8CAYAAADOF0UhAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAP+6SURBVHhe7N1nzGXVmeb9VVBAkUORc84551xgMGAbg9vY07ax3e6eHlnd0kgjvfOhJfv98GqYkaa73TPOBhvbYBMMmJxzhiKHIudMkXN4+S0/q7zrcJ5Q4cBTxfWXtuo5O6xwr7X3fe37XufUhP/9v//3Ce+99966b731VgkhhBBCCHOPhRdeuCy66KJ3T/hf/+t/Tfvggw82mDRpUpkwYcLQ4RBCCCGEMCd8pK/Ke++9Z5s64Zhjjpm20EILbbDeeuuVJZZYYuiUEEIIIYQwJ7z22mvlkUceKS+99NJfBNekSZM22GKLLcryyy8/dEoIIYQQQpgTXnzxxXL77beX6dOnT11gaF8IIYQQQhgQEVwhhBBCCAMmgiuEEEIIYcBEcIUQQgghDJgIrhBCCCGEARPBFUIIIYQwYCK4QgghhBAGTARXCCGEEMKAieAKIYQQQhgwEVwhhBBCCAMmgiuEEEIIYcBEcIUQQgghDJgIrhBCCCGEARPBFUIIIYQwYCK4QgghhBAGTARXCCGEEMKAieAKIYQQQhgwEVwhhBBCCANmwSlTpnx/4sSJk1daaaWy2GKLDe0enddff71cffXV5corryyrrbbamK595513yo033lh+9atflcsuu6y88MILZZNNNhk6+slwxx13lLPOOqu8++675bnnnisrr7zy0JGP4/h5551XbrrppjH3MYQQQggBb775Znn22WfLW2+99fRsRbiILULrxz/+cbn88svLq6++OnRkZO66665y/vnnlzXXXLO8//77VfhMmzZt6Ohg0eZLL720nH322WWFFVao9V9xxRVVUPXjySefLKeffnoVaNq78MILDx0JIYQQQpg1ZktwTZw4sSy33HJVuIyVl19+uUydOrW899575aijjirf+c536r9LLrnk0B
 * 
 */

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- MyCanvasManager 和 MyCanvas 类定义

/**
 * MyCanvas 对象管理类
 * @param uniqueId 唯一ID
 */
function MyCanvasManager(uniqueId) {
	this.uniqueId = uniqueId; // 唯一ID
	this.canvasArr = null; // 全局 canvas 注册列表
	/**
	 * 初始化 canvas 注册表
	 * @param size 初始化注册表容量
	 */
	this.init = function(size) {
		this.canvasArr = new Array(size);
	}
	/**
	 * 注册 canvas 对象
	 * @param index 唯一索引序号
	 * @param canvas canvas 对象
	 */
	this.register = function(index, canvas) {
		this.canvasArr[index] = canvas;
	}
	/**
	 * 获取 canvas 对象
	 * @param index 唯一序号
	 */
	this.getCanvas = function(index) {
		return this.canvasArr[index];
	}
	/**
	 * 清空注册表
	 */
	this.clear = function() {this.canvasArr = null;}
	/**
	 * 获取所有注册的 canvas
	 */
	this.getAllCanvas = function() {
		var registedCanvas = [];
		for (var i = 0; i < this.canvasArr.length; i++) {
			if (this.canvasArr[i]) {
				registedCanvas.push(this.canvasArr[i]);
			}
		}
		return registedCanvas;
	}
	/**
	 * 获取鼠标聚焦的 canvas
	 */
	this.queryFocusedCanvas = function() {
		var myCanvas = null;
		for (var i = 0; i < this.canvasArr.length; i++) {
			myCanvas = this.canvasArr[i];
			if (myCanvas && myCanvas.isFocused) {
				return myCanvas;
			}
		}
		return null;
	}
	/**
	 * 销毁所有注册的 myCanvas
	 */
	this.destroyAllCanvas = function() {
		for (var i = 0; i < this.canvasArr.length; i++) {
			var myCanvas = this.canvasArr[i];
			if (myCanvas && myCanvas.destory) {
				myCanvas.destory();
			}
			this.canvasArr[i] = null;
		}
	}
}
/**
 * 自定义的 canvas 封装类
 * @param index 唯一序号
 */
function MyCanvas(index) {
	this.index = index; // 对象唯一编号
	this.canvas = null; // canvas 标签对象
	this.scale = 2; // 按比例增大分辨率倍数（影响 html2canvas 的截图分辨率）
	this.context = null; // canvas 上下文
	this.canvasHistory = []; // 绘制历史记录
	this.drawStep = 0; // 步数记录，0位图像作为基图像快照，不可删除
	this.strokeColor = "red"; // 画笔颜色
	this.lineWidth = 1 * this.scale; // 画笔粗细（按比例缩放）
	this.isMouseDown = false; // 鼠标按下状态
	this.lastLoc = {x:0, y:0}; // 鼠标轨迹末端
	this.isMoved = false; // 记录每次画笔是否移动
	this.isFocused = false; // 鼠标是否聚焦（悬停）在 canvas 标签区域
	this.drawFrame = null; // 主框架 'draw_frame' 标签 jQuery对象（用于标签动态调整）
	this.drawControl = null; // 控制区 'draw_control' 标签 jQuery对象
	this.drawHtmlOrigin = null; // 画图面板 'draw_html_origin' 标签 jQuery对象
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- myCanvas 初始化区

// 全局 myCanvas 管理对象
var myCanvasManager = null;

/** 
 * 加载绘制模块 canvas
 * @param $drawFrame 画图主框架（jQuery对象）
 * @param index 唯一序号，必须不同
 * @param lastEditImgDataUrl 原先编辑过的历史快照 base64格式（来源数据库）
 * @param $scrollElem 当前页面所处的滚动窗口元素（jQuery对象）
 * 		
 * 		$scrollElem 元素作用：
 * 			用于控制其滚动条滚动位置，以适应被截图元素能完整截图
 * 			注：被截图元素的顶边必须位于 window 顶之下，否则位于 window 顶之上的不可视区域将截取不到
 * 		$scrollElem 元素使用方式，如：
 * 			1) vue 中 el-dialog 标签（支持滚动），获取其对象为：
 * 			  var $el_dialog_body = $($('.el-dialog__wrapper').get(1));
 * 		==>	  var $scrollElem = $el_dialog_body  <==== 此处！！！
 * 		传入此方法：
 * 			2) 获取需要被截图的元素 $drawHtmlOrigin 相对 window 顶端的偏移
 * 			  var offsetTop = $drawHtmlOrigin.offset().top;
 *			3) 设置 scrollElem 滚动条滚动到元素 $drawHtmlOrigin 的顶端，使其完全显示在屏幕中
 * 			  $scrollElem.scrollTop($scrollElem.scrollTop() + offsetTop);
 */
function loadCanvas($drawFrame, index, lastEditImgDataUrl, $scrollElem) {
	var myCanvas = myCanvasManager.getCanvas(index);
	if (myCanvas) {
		return; // myCanvas 已存在，则结束，防止重复创建
	}
	// 获取画图框架子元素对象
	var $children = $drawFrame.children();
	var $drawHtmlOrigin = $($children.get(0));
	var $drawControl = $($children.get(2));
	// 构建 myCanvas 对象
	var myCanvas = new MyCanvas(index);
	// 缓存标签对象，用于标签动态调整
	myCanvas.setDrawFrame($drawFrame);
	myCanvas.setDrawHtmlOrigin($drawHtmlOrigin);
	myCanvas.setDrawControl($drawControl);

	/**
	 *  自动调整 draw_html_origin 宽高，适应内部文本宽高
	 * 	  1. html2canvas 快照功能按照 draw_html_origin div 的宽高进行截屏
	 *    2. draw_html_origin 初始化时没有固定宽高，内部文本有溢出 draw_html_origin div 时，会导致部分截屏
	 */
	$drawHtmlOrigin.width($drawHtmlOrigin.get(0).scrollWidth); // 此固定值在销毁 destroy() 中被置为 100%，取消固定高度
	//$drawHtmlOrigin.height(drawHtmlOrigin.scrollHeight); // 不可固定此 div 属性，会导致其它数据加载进来时不能自适应高度

	/**
	 *  获取待截图元素 $drawHtmlOrigin 偏移 window 顶的偏移量，
	 *  以使 $scrollElem 修正滚动条位置，使 $drawHtmlOrigin 元素 top边 保证在 window top 之下
	 */ 
	var offsetTop = $drawHtmlOrigin.offset().top;
	if (offsetTop < 0) {
		//console.log(offsetTop, $scrollElem.scrollTop(),$scrollElem.scrollTop() + offsetTop);
		$scrollElem.scrollTop($scrollElem.scrollTop() + offsetTop);
	}

	/**
	 * （核心功能）实现了 html 元素转快照（截图）的功能
	 */
	// 将 drawHtmlOrigin 容器转为 canvas
	html2canvas($drawHtmlOrigin, {
		//dpi: 192, // 将分辨率提高到特定的 DPI（每英寸点数），默认：96
		//scale: 2 // 按比例增大分辨率倍数，与 dpi 同样效果（dpi=96[优先] 等效 scale=1）
		scale: myCanvas.scale
	}).then(function(canvas) {
		// 将 canvas 封装到 myCanvas，并初始化
		myCanvas.init(canvas);
		// 加载原先编辑过的历史快照（数据库获取）
		if (lastEditImgDataUrl) {
			myCanvas.loadLastEditCanvasImage(lastEditImgDataUrl);
		}
	});
}

/**
 * MyCanvas 初始化
 * @param canvas canvas 对象
 */
MyCanvas.prototype.init = function(canvas) {
	// 注册 MyCanvas
	myCanvasManager.register(this.index, this);
	
	// 设置 canvas 属性、样式
	this.canvas = canvas;
	this.context = canvas.getContext("2d"); // 获取 canvas 上下文
	this.canvas.id = "canvas_" + this.index;
	this.canvas.className = 'draw_canvas';
	// 将 canvas 插入 drawHtmlOrigin 前面，并悬浮 float ，呈现覆盖 drawHtmlOrigin 的效果
	$(this.canvas).insertBefore(this.drawHtmlOrigin);

	// 注册鼠标事件
	var myCanvas = this;
	this.canvas.onmousedown = function(e){
		//console.log("mouse down");
		//e.preventDefault();
		// console.log(this); // canvas 事件中的 this 代表 canvas 对象本身
		myCanvas.beginStroke({x: e.clientX, y: e.clientY});
		//console.log(myCanvas.canvasHistory.length, myCanvas.drawStep);
	}
	this.canvas.ondblclick = function(e) {
		/**
		 * 鼠标双击，在鼠标处弹出文本输入框
		 * 之后点击输入框外，结束输入
		 */
		 //console.log("mouse dblclick");
		// 在 canvas 指定位置上显示文本输入框
		myCanvas.showInputTextAtCanvas({x: e.clientX, y: e.clientY});
	}
	document.onmouseup = function(e){
		/* 
		 * 鼠标释放事件必须全文档 document 监听
		 * 因为绘制时，鼠标移出，并且在 canvas 外释放，这时就需要全文档监听
		 * 以便 canvas 外的鼠标释放时能结束绘制
		 */
		// console.log("mouse up");
		// e.preventDefault();
		var list = myCanvasManager.getAllCanvas();
		var myCanvas = null;
		for (var i = 0; i < list.length; i++) {
			myCanvas = list[i];
			if (myCanvas.isMouseDown) {
				myCanvas.isMouseDown = false;
				myCanvas.endStroke();
			}
		}
		//console.log(myCanvas.canvasHistory.length, myCanvas.drawStep);
	};
	this.canvas.onmouseover = function(e){
		//console.log("mouse over");
		//e.preventDefault();
		myCanvas.isFocused = true; // 注册鼠标聚焦
	};
	this.canvas.onmouseout = function(e){
		//console.log("mouse out");
		//e.preventDefault();
		myCanvas.isFocused = false; // 解除鼠标聚焦
	};
	this.canvas.onmousemove = function(e){
		e.preventDefault();
		if(myCanvas.isMouseDown) {
			// console.log("mouse move");
			myCanvas.moveStroke({x: e.clientX, y: e.clientY});
		}
	};
	// 全文档键盘监听事件
	document.onkeydown = function(e) {
		/**
		 * 满足下面两个条件时，才对事件的全局默认行为阻止，否则不阻止
		 *   1. 只有监听到特定组合按键时
		 *   2. 只有获取到鼠标焦点的 canvas 时
		 * 注：阻止是为了不让事件在 canvas 区域生效后，继续影响全局其它标签（特别是输入标签）
		 */
        var keyCode = e.keyCode || e.which || e.charCode;
        var ctrlKey = e.ctrlKey || e.metaKey;
		if(ctrlKey && keyCode == 90) { //'ctrl + z'
			var myCanvas = myCanvasManager.queryFocusedCanvas(); // 只有获取到鼠标焦点的 canvas 时，才对事件的全局默认行为阻止
			if (myCanvas) {
				// 后退（撤销）绘制操作
				myCanvas.canvasUndo();
				e.preventDefault(); // 阻止默认动作，避免将事件传到 canvas 外，如 canvas 外的 input 输入框
			}
        }
		if(ctrlKey && keyCode == 89) { //'ctrl + y'
			var myCanvas = myCanvasManager.queryFocusedCanvas();
			if (myCanvas) {
				// 前进（反撤销）绘制操作
				myCanvas.canvasRedo();
				e.preventDefault();
			}
		}
		if(ctrlKey && keyCode == 83) { //'ctrl + s'
			var myCanvas = myCanvasManager.queryFocusedCanvas();
			if (myCanvas) {
				// 保存并提交图片
				myCanvas.canvasSaveAndSubmit(subjId, studId);
				e.preventDefault();
			}
		}
        //e.preventDefault(); // 不能写，会使全局文本输入框无法输入！！
        return true;
	};
	var imgDataUrl = this.getCanvasDataUrl();
	// 添加原始图像快照到历史记录
	this.canvasHistory.push(imgDataUrl);
	// 在历史区显示基图像快照缩略图
	//this.showCanvasBaseImageInHistory(imgDataUrl);
	// 异步请求最新一次的编辑快照
	//this.showCanvasEditImageInHistory('');
	// 自适应调整框架
	//this.adjustCanvasFrame();
}
// 保存 draw_frame draw_html_origin draw_control 进对象
MyCanvas.prototype.setDrawFrame = function(drawFrame) {
	this.drawFrame = drawFrame;
}
MyCanvas.prototype.setDrawControl = function(drawControl) {
	this.drawControl = drawControl;
}
MyCanvas.prototype.setDrawHtmlOrigin = function(drawHtmlOrigin) {
	this.drawHtmlOrigin = drawHtmlOrigin;
}
// 自动调整框架高度
MyCanvas.prototype.adjustCanvasFrame = function() {
	/**
	 * canvas.style.height 为浏览器渲染高度（即显示高度）
	 * canvas.height 为画布实际高度
	 */
	var height = this.canvas.style.height + this.drawControl.height();
	this.drawFrame.height(height);
}
// 销毁 myCanvas 对象
MyCanvas.prototype.destory = function() {
	// 清除 canvas 标签对象
	$(this.canvas).remove();
	// 初始化 drawHtmlOrigin div 宽高为 100% ，避免固定高度影响其动态适应
	this.drawHtmlOrigin.width('100%');
	this.drawHtmlOrigin.height('100%');
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- 画笔功能区

// 开始绘画状态
MyCanvas.prototype.beginStroke = function(point){
	this.isMouseDown = true;
	var curLoc = this.windowToCanvas(point.x, point.y);
	this.lastLoc = curLoc;
}
// 结束绘画状态
MyCanvas.prototype.endStroke = function() {
	this.isMouseDown = false;
	if (this.isMoved) {
		this.drawStepIncrease(); // 结束绘制时加入快照历史
		this.isMoved = false;
	}
}
// 移动画笔绘画
MyCanvas.prototype.moveStroke = function(point) {
	this.isMoved = true;
	var curLoc = this.windowToCanvas(point.x, point.y);
	// 前后位置没变，则不做绘制处理
	if (curLoc.x == this.lastLoc.x && curLoc.y == this.lastLoc.y) {
		this.isMoved = false;
		return;
	}
	var context = this.context;
	context.beginPath();
	context.moveTo(this.lastLoc.x, this.lastLoc.y);
	context.lineTo(curLoc.x, curLoc.y);
	context.strokeStyle = this.strokeColor;
	context.lineWidth = this.lineWidth;
	context.lineCap = "round";
	context.lineJoin = "round";
	context.stroke();
	//console.log(this.lastLoc, curLoc);
	this.lastLoc = curLoc;
}
/** 
 * 鼠标坐标 转到 canvas 画布坐标，并按照 scale 进行按比例缩放
 *   以 canvas.width / canvas.height 为边界
 */
MyCanvas.prototype.windowToCanvas = function(x, y) {
	var bbox = this.canvas.getBoundingClientRect();
	return {x : Math.round(x-bbox.left) * this.scale, y : Math.round(y-bbox.top) * this.scale}
}
/** 
 * 鼠标坐标 转到 canvas 显示坐标
 *   以 canvas.style.width / canvas.style.height 为边界
 */
MyCanvas.prototype.windowToDisplayCanvas = function(x, y) {
	var bbox = this.canvas.getBoundingClientRect();
	return {x : Math.round(x-bbox.left), y : Math.round(y-bbox.top)}
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- 文本输入功能区

// 在 canvas 指定位置上显示文本输入框
MyCanvas.prototype.showInputTextAtCanvas = function(point) {
	// 将新建的 Text 插入到 canvas 前面（样式上做浮动控制）
	var $input = $(this.createInputTextElem(point));
	$input.insertBefore(this.canvas);
	$input.focus(); // 自动聚焦（必须渲染之后再设置聚焦才生效）
}
// 创建文本框元素
MyCanvas.prototype.createInputTextElem = function(point) {
	var input = document.createElement('input');
	input.type = "text";
	input.className = 'draw_input_text';
	//console.log(point);
	// 设置 input 标签位置到鼠标位置
	var displayPoint = this.windowToDisplayCanvas(point.x, point.y);
	//console.log(displayPoint);
	input.style.left = displayPoint.x + 'px';
	input.style.top = displayPoint.y - 2 + 'px'; // （垂直方向上）对齐鼠标指针头
	// 设置失去焦点事件
	var myCanvas = this;
	input.onblur = function() {
		// console.log(this); // 此处 this 代表 input 标签对象本身
		// 获取文本内容
		var textValue = input.value;
		if (textValue && textValue.trim()) { // 只处理非空白文本
			// Text ==> canvas
			myCanvas.sinkTextIntoCanvas(textValue, this.size, point); // 传入自身对象
			// 绘制历史步数增加
			myCanvas.drawStepIncrease();
		}
		// 删除 input 标签
		$(input).remove();
	}
	return input;
}
/**
 * 将 text 文本框文本内容 沉浸到 canvas 中，失去焦点时触发
 * @param textValue 文本内容
 * @param fontSize 字体大小
 * @param point window鼠标坐标
 */
MyCanvas.prototype.sinkTextIntoCanvas = function(textValue, fontSize, point) {
	var canvasPoint = this.windowToCanvas(point.x, point.y);
	//console.log(canvasPoint);
	//console.log(textValue);
	var context = this.context;
	context.fillStyle = this.strokeColor;
	//context.font = '20px SimHei'; // 黑体
	context.font = fontSize * this.scale + 'px SimHei'; // 字体大小按比例缩放，黑体
	context.fillText(textValue, canvasPoint.x+1*this.scale, canvasPoint.y+17*this.scale); // 位置有偏差（加上修正值），注：使用 strokeText() 会出现空心字
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- 绘图交互区

/** 
 * 获取 canvas 显示图片 data url
 * @return
 */
MyCanvas.prototype.getCanvasDataUrl = function(quality) {
	var imgDataUrl = '';
	if (this.canvas) {
		// 使用 toDataURL 方法将图像转换为 base64 编码的 URL 字符串
		imgDataUrl = this.canvas.toDataURL('image/png');
	}
	return imgDataUrl;
}
/**
 * 设置 canvas 显示图片 data url
 * @param imgDataUrl base64文本格式
 * @param needStepIncrease true|false 是否需要计步
 */
MyCanvas.prototype.setCanvasDataUrl = function(imgDataUrl, needStepIncrease) {
	var myCanvas = this; // 将对象传递进 img 事件中
	var canvasImg = new Image();
	canvasImg.src = imgDataUrl;
	canvasImg.onload = function() {
		myCanvas.context.clearRect(0, 0, myCanvas.canvas.width, myCanvas.canvas.height);
		myCanvas.context.drawImage(canvasImg, 0, 0);
		if (needStepIncrease) {
			myCanvas.drawStepIncrease();
		}
	};
}
/**
 * 加载先前编辑过的图片到 canvas
 * @param lastEditImgDataUrl base64文本格式
 */
MyCanvas.prototype.loadLastEditCanvasImage = function(lastEditImgDataUrl) {
	if (lastEditImgDataUrl) {
		this.setCanvasDataUrl(lastEditImgDataUrl, true); // true 增加历史步数
	}
}
// 绘制历史步数增加
MyCanvas.prototype.drawStepIncrease = function() {
	this.drawStep++;
    if (this.drawStep < this.canvasHistory.length) {
    	this.canvasHistory.length = this.drawStep; // 截断数组
    }
    this.canvasHistory.push(this.getCanvasDataUrl()); // 添加新的绘制快照到历史记录
}
// 撤销
MyCanvas.prototype.canvasUndo = function() {
	if (this.drawStep >= 1) { // 从1开始，0位为基图像快照
		this.drawStep--;
		// canvas 设为 drawStep 步的快照
		this.setCanvasDataUrl(this.canvasHistory[this.drawStep]);
	} else {
		console.log('不能再继续撤销了');
	}
}
// 反撤销
MyCanvas.prototype.canvasRedo = function() {
	if (this.drawStep < this.canvasHistory.length - 1) {
		this.drawStep++;
		// canvas 设为 drawStep 步的快照
		this.setCanvasDataUrl(this.canvasHistory[this.drawStep]);
	} else {
		console.log('已经是最新的记录了');
	}
}
// 恢复基图像快照（清除绘制操作）
MyCanvas.prototype.canvasRestore = function() {
	// canvas 设为基图像快照，并将此基快照加入历史记录
	this.setCanvasDataUrl(this.canvasHistory[0], true);
}
// 保存并提交 canvas 图片
MyCanvas.prototype.canvasSaveAndSubmit = function() {
	// 提交 canvas 图片
	//this.submitLatestImageUrl();
	// 显示编辑后的缩略图
	//this.showCanvasEditImageInHistory(this.getCanvasDataUrl());
}
// 编辑图像（最新快照）缩略图显示（只显示一张编辑图像，重叠覆盖，保留最新）
MyCanvas.prototype.showCanvasEditImageInHistory = function(imgDataUrl) {
	if (!imgDataUrl) {
		return;
	}
	var imgEdit = document.querySelector('#draw_img_edit');
	// 创建图片元素
	var imgElem = this.createImageElem(imgDataUrl);
	// 先删除原有图像，再添加新编辑的图像
	var childImgs = imgEdit.childNodes;
	if (childImgs.length > 0) {
		imgEdit.removeChild(childImgs[0]);
	}
	// 图片元素嵌入到 div 中
	if (imgElem) {
		imgEdit.appendChild(imgElem);
	}
}
// 原始图像（原始快照）缩略图显示（基图像，供编辑参考使用）
MyCanvas.prototype.showCanvasBaseImageInHistory = function(imgDataUrl) {
	var imgBase = document.querySelector('#draw_img_base');
	// 创建图片元素
	var imgElem = this.createImageElem(imgDataUrl);
	// 图片元素嵌入到 div 中
	imgBase.appendChild(imgElem);
}
// 创建图片元素
MyCanvas.prototype.createImageElem = function(imgDataUrl) {
	var myCanvas = this; // 将对象传递进 img 事件中
	var img = document.createElement('img');
	img.src = imgDataUrl;
	img.className = 'draw_img';
	img.onclick = function() {
		// 缩略图点击事件
		myCanvas.setCanvasDataUrl(imgDataUrl, true);
	}
	return img;
}
// 下载 canvas 元素的图片
MyCanvas.prototype.downloadCanvasIamge = function(name) {
    // 获取 canvas image data url
    var url = this.getCanvasDataUrl();
	
	if(window.navigator.msSaveOrOpenBlob) {
		/** 
		 * 1. IE方式下载
		 */
		// 截取base64的数据内容（去掉前面的描述信息，类似这样的一段：data:image/png;base64,）并解码为2进制数据
		var bstr = atob(url.split(',')[1]);
		// 获取解码后的二进制数据的长度，用于后面创建二进制数据容器
		var n = bstr.length;
		// 创建一个Uint8Array类型的数组以存放二进制数据
		var u8arr = new Uint8Array(n);
		// 将二进制数据存入Uint8Array类型的数组中
		while (n--) {
			u8arr[n] = bstr.charCodeAt(n);
		}
		// 创建blob对象
		var blob = new Blob([u8arr]);
		// 调用浏览器的方法，调起IE的下载流程
		window.navigator.msSaveOrOpenBlob(blob, (name || 'image-download') + '.' + 'png');
	} else {
		/** 
		 * 2. 非IE方式下载
		 */
		// 生成一个a元素
		var a = document.createElement('a');
		// 创建一个单击事件
		var event = document.createEvent("MouseEvent");
		event.initEvent("click", false, false); //initEvent 不加后两个参数在FF下会报错
		// 将a的download属性设置为我们想要下载的图片名称，若name不存在则使用‘image-download’作为默认名称
		a.download = name || 'image-download';
		// 将生成的URL设置为a.href属性
		a.href = url;
		// 触发a的单击事件
		a.dispatchEvent(event);
	}
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- 按钮事件区

/** 
 * 按钮注册事件
 * @param btns 按钮集
 * @param index 序号
 */
function registerBtnEvent(btns, index) {
	//console.log(btns);
	var uniqueSuffix = '_' + index; // 唯一后缀
	// 1. 向每个按钮上的 id 加上唯一后缀
	for(var i=0; i< btns.length; i++) {
		btns.get(i).setAttribute('id', btns.get(i).id + uniqueSuffix);
	}
	// 2. 向按钮注册事件
	$("#draw_export_btn" + uniqueSuffix).click(
		function(e){
			// 从 canvas 对象管理中获取 MyCanvas
			var myCanvas = myCanvasManager.getCanvas(index); // 将对象传递进 btn 事件中
			// 导出（下载）canvas 图片
			myCanvas.downloadCanvasIamge('图片名称');
		}
	)
	$("#draw_back_btn" + uniqueSuffix).click(
		function(e){
			// 从 canvas 对象管理中获取 MyCanvas
			var myCanvas = myCanvasManager.getCanvas(index); // 将对象传递进 btn 事件中
			// 后退（撤销）绘制操作
			myCanvas.canvasUndo();
		}
	)
	$("#draw_forward_btn" + uniqueSuffix).click(
		function(e){
			// 从 canvas 对象管理中获取 MyCanvas
			var myCanvas = myCanvasManager.getCanvas(index); // 将对象传递进 btn 事件中
			// 前进（反撤销）绘制操作
			myCanvas.canvasRedo();
		}
	)
	$("#draw_restore_btn" + uniqueSuffix).click(
		function(e){
			// 从 canvas 对象管理中获取 MyCanvas
			var myCanvas = myCanvasManager.getCanvas(index); // 将对象传递进 btn 事件中
			// 恢复初始状态（清除所有绘制操作）
			myCanvas.canvasRestore();
		}
	)
}

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -- 对外接口

/**
 * 初始化 myCanvasManager 管理对象
 * @param size 初始化 canvas 个数
 * @param uniqueId 唯一ID
 */
function myCanvasManagerInit(size, uniqueId) {
	//console.log(size, uniqueId);
	// 销毁 myCanvasManager
	if (myCanvasManager) {
		if (myCanvasManager.uniqueId == uniqueId) {
			return; // 相同 uniqueId 的对象保留
		}
		myCanvasManager.destroyAllCanvas();
		myCanvasManager = null;
	}
	// 重建 myCanvasManager
	myCanvasManager = new MyCanvasManager(uniqueId);
	myCanvasManager.init(size);
}
/**
 * 获取 canvas data url
 * @param index 唯一序号
 */
function getCanvasDataUrl(index) {
	// 从 canvas 对象管理中获取 MyCanvas
	var myCanvas = myCanvasManager.getCanvas(index);
	var imgDataUrl = '';
	if (myCanvas) {
		// 从 myCanvas 获取 data url
		imgDataUrl = myCanvas.getCanvasDataUrl();
	}
	// 返回 data url
	return imgDataUrl;
}
/** 
 * “批改”按钮触发事件
 * @param correct_btn 批改按钮
 * @param index 序号
 * @param lastEditImgDataUrl 原先编辑过的快照 base64格式
 * @param $scrollElem 当前页面所处的滚动窗口元素（jQuery对象）
 */
function draw_correct_btn_click(correct_btn, index, lastEditImgDataUrl, $scrollElem) {
	// 获取按钮所属区域的主框架，并加载 canvas
	var $drawFrame = $(correct_btn).parent().parent().parent();
	loadCanvas($drawFrame, index, lastEditImgDataUrl, $scrollElem);
	// 获取“批改”按钮的兄弟按钮集，注册按钮事件
	var $btns = $(correct_btn).parent().children();
	registerBtnEvent($btns, index);
}
/**
 * 编辑快照加载事件
 * @param $thisImg 编辑快照 img 对象（jQuery对象）
 */
function draw_html_edited_load($thisImg) {
	// 设置 编辑快照 img 的宽高 为 draw_html_origin div 的宽高
	var draw_html_origin =  $thisImg.parent().prev().get(0);
	$thisImg.width(draw_html_origin.scrollWidth);
	$thisImg.height(draw_html_origin.scrollHeight);
}

// 导出接口
export { 
	myCanvasManagerInit,
	getCanvasDataUrl,
	draw_correct_btn_click,
	draw_html_edited_load
}