<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-select v-model="filters.termname" filterable placeholder="请选择学期" @change="termchange">
					<el-option label="不限" value="-"></el-option>
				    <el-option
				      v-for="item in termoption"
				      :key="item.id"
				      :label="item.name"
				      :value="item.name">
				    </el-option>
				</el-select>
				<el-select v-model="filters.classname" filterable placeholder="请选择班级" @change="classChangeSelect">
				    <el-option
				      v-for="item in classoption"
				      :key="item.value"
				      :label="item.label"
				      :value="item.value">
				    </el-option>
				</el-select>
				<el-select v-model="filters.exid" filterable placeholder="请选择实验" @change="changeSelect">
				    <el-option
				      v-for="item in options"
				      :key="item.id"
				      :label="item.ename"
				      :value="item.id">
				    </el-option>
				</el-select>
				<el-form-item>
					<el-button type="primary" v-on:click="getUsers">查询</el-button>
					<el-button type="danger" v-on:click="UploadCourse" v-show="downloadFlag">下载</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="users" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;"  height="500">
			<el-table-column type="index" width="50">
			</el-table-column>
			<el-table-column prop="num" label="学号" width="100" show-overflow-tooltip align="center">
			</el-table-column>
			<el-table-column prop="name" label="姓名" width="120" show-overflow-tooltip align="center">
			</el-table-column>
			<el-table-column prop="class" label="班级" width="120" show-overflow-tooltip align="center">
			</el-table-column>
			<el-table-column prop="stime" label="签到时间" width="180" sortable show-overflow-tooltip align="center">
			</el-table-column>
			<el-table-column prop="etime" label="签退时间"  width="180" show-overflow-tooltip align="center">
			</el-table-column>
			<el-table-column prop="duration" label="实验时长" width="120" show-overflow-tooltip sortable align="center">
			</el-table-column>
			<el-table-column prop="score" label="实验预习" width="120" show-overflow-tooltip  align="center" sortable>
				<template slot-scope="scope">
					{{scope.row.score.toFixed(2)}}
				</template>
			</el-table-column>
			<el-table-column prop="operationscore" label="实验操作" width="120" show-overflow-tooltip  align="center" sortable>
				<template slot-scope="scope">
					{{scope.row.operationscore.toFixed(2)}}
				</template>
			</el-table-column>
			<el-table-column prop="reportscore" label="实验报告" width="120" show-overflow-tooltip  align="center" sortable>
				<template slot-scope="scope">
					{{scope.row.reportscore.toFixed(2)}}
				</template>
			</el-table-column>
			<el-table-column prop="rationscore" label="总分" width="110" show-overflow-tooltip  align="center" sortable>
				<template slot-scope="scope">
					{{scope.row.rationscore.toFixed(2)}}
				</template>
			</el-table-column>
			<el-table-column label="操作" width="500">
				 <template slot-scope="scope">
          <div class="flex-center">
          <el-button size="small" @click="handleExprementObjecctive(scope.$index, scope.row)">客观题批改</el-button>
          <el-button size="small" @click="handleExprement(scope.$index, scope.row)">主观题批改</el-button>
          <el-button type="danger" size="small" @click="handleResetReport(scope.$index, scope.row)">报告打回</el-button>
					<el-button type="primary" size="small" @click="handleEdit(scope.$index, scope.row)">报告查看</el-button>
					<el-button type="danger" size="small" @click="handleReExame(scope.$index, scope.row)">重置预习</el-button>
          </div>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">全部数据</el-button>
			<!-- <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
			</el-pagination> -->
		</el-col>

		<!--实验报告查看界面-->
		<el-dialog title="实验报告" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<div id="pdfDom"  ref="resume">
				<p style="text-align: center;line-height: 50px;font-size: 20px;font-weight: bold">{{ExprementName}}</p>
				<p style="text-align: center;line-height: 50px;">
					<span>班级：</span><span style="text-decoration:underline;">&nbsp;{{filters.classname}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
					<span>姓名：</span><span  style="text-decoration:underline;">&nbsp;{{editForm.name}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
					<span>得分:</span>&nbsp;&nbsp;<span  style="text-decoration:underline;">&nbsp;&nbsp;{{editForm.reportscore}} &nbsp;&nbsp;</span>
				</p>
				<div v-for = "item in stuExprement">
					<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">{{item.retitle}}</div>
					<div style="padding: 10px 15px;" v-html="item.recont"></div>
				</div>
				<div v-if="ObjectiveQuestions.length > 0 && ObjectiveQuestions[0].isHandIn == 1">
					<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">客观题</div>
					<div v-for="(question, questionIndex) in ObjectiveQuestions">
						<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">
							{{questionIndex+1}}. 
							<span style="color: red"> 
								[{{question.type == 1 ? '单选题' : question.type == 2 ? '多选题' : '填空题'}}]
								{{question.score}}分
							</span> 
							{{question.topic}}
						</div>
						<div class="pselectdiv" style="padding: 10px 15px;">
							<div>
								<span>正确答案：</span>
								<span v-if="question.type == 1"><!-- 单选题 -->
									{{num[question.correctAnswerValue]}}
								</span>
								<span v-else-if="question.type == 2" v-for="correct in question.correctAnswerValue"><!-- 多选题 -->
									{{num[correct]}}
								</span>
								<span v-else-if="question.type == 3"><!-- 填空题 -->
									{{question.correctAnswerValue}}
								</span>
								<p v-if="question.type == 3"></p>
								<span>你的答案：</span>
								<span v-if="question.type == 1">
									{{num[question.questionResult.answerValue] ? num[question.questionResult.answerValue] : '未选择'}}
								</span>
								<span v-else-if="question.type == 2" v-for="answer in question.questionResult.answerValue">
									{{num[answer] ? num[answer] : '未选择'}}
								</span>
								<span v-else-if="question.type == 3">
									{{question.questionResult.answerValue ? question.questionResult.answerValue : '未填写'}}
								</span>
								<span :class="[question.questionResult.isRight == 1 ? activeClass : errorClass]">
									({{question.questionResult.isRight == 1 ? '正确' : (question.questionResult.isRight == 2 ? '部分正确' : '错误')}})
								</span>
							</div>
              <div class="questionImageClass" v-if="question.topicImages" v-for="(image, imageIndex) in question.topicImages">
                <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
                <img :src="image.imageBase64Content" style="height:50%;width:50%;">
              </div>
							<div v-if="question.questionResult.isRight == 1">
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? activeBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
							<div v-else>
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? errorBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
						</div>
						<div style="font-size: 14px;padding:10px 15px;font-weight: bold;">
							<span>(本题得分:{{question.questionResult.score}}分) </span>
						</div>
					</div>
					<p></p>
				</div>
				<div class="title">实验思考</div>
				<div v-for = "(item, index) in stuExpreport">
					<div style="font-size: 14px;padding:10px 15px;font-weight: bold;">
						{{index+1}}.&nbsp;{{item.topic}}<span>(总分:{{item.Allscore}}分)</span>
					</div>
          <div class="questionImageClass" v-if="item.topicImages" v-for="(image, imageIndex) in item.topicImages">
            <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
            <img :src="image.imageBase64Content" style="height:50%;width:50%;">
          </div>
					<div v-if="!item.mark" v-html="item.content" style="padding-left: 30px;min-height: 140px"></div>
					<img v-if="item.mark" :src="item.mark" width="100%" height="100%" style="padding-left: 30px;"/><!-- 宽高 100% 可适应不同分辨率的图片 -->
					<div style="font-size: 14px;padding:10px 15px;font-weight: bold;">
						<span>(本题得分:{{item.score}}分)</span>
					</div>
				</div>
			</div>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="dowloadExperiment()" :loading="editLoading">下载</el-button>
			</div>
		</el-dialog>

    <!--客观题批改界面-->
		<el-dialog title="实验客观题" :visible.sync="objectiveCorrectFormVisible" :close-on-click-modal="false">
			<div id="pdfDom"  ref="resume">
				<p style="text-align: center;line-height: 50px;font-size: 20px;font-weight: bold">{{ExprementName}}</p>
				<p style="text-align: center;line-height: 50px;">
					<span>班级：</span><span style="text-decoration:underline;">&nbsp;{{filters.classname}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
					<span>姓名：</span><span  style="text-decoration:underline;">&nbsp;{{currentStuentName}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
				</p>
				<div v-if="ObjectiveQuestions.length > 0 && ObjectiveQuestions[0].isHandIn == 1">
					<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">客观题</div>
					<div v-for="(question, questionIndex) in ObjectiveQuestions">
						<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">
							{{questionIndex+1}}. 
							<span style="color: red"> 
								[{{question.type == 1 ? '单选题' : question.type == 2 ? '多选题' : '填空题'}}]
								{{question.score}}分
							</span> 
							{{question.topic}}
						</div>
						<div class="pselectdiv" style="padding: 10px 15px;">
							<div>
								<span>正确答案：</span>
								<span v-if="question.type == 1"><!-- 单选题 -->
									{{num[question.correctAnswerValue]}}
								</span>
								<span v-else-if="question.type == 2" v-for="correct in question.correctAnswerValue"><!-- 多选题 -->
									{{num[correct]}}
								</span>
								<span v-else-if="question.type == 3"><!-- 填空题 -->
									{{question.correctAnswerValue}}
								</span>
								<p v-if="question.type == 3"></p>
								<span>你的答案：</span>
								<span v-if="question.type == 1">
									{{num[question.questionResult.answerValue] ? num[question.questionResult.answerValue] : '未选择'}}
								</span>
								<span v-else-if="question.type == 2" v-for="answer in question.questionResult.answerValue">
									{{num[answer] ? num[answer] : '未选择'}}
								</span>
								<span v-else-if="question.type == 3">
									{{question.questionResult.answerValue ? question.questionResult.answerValue : '未填写'}}
								</span>
								<span :class="[question.questionResult.isRight == 1 ? activeClass : errorClass]">
									({{question.questionResult.isRight == 1 ? '正确' : (question.questionResult.isRight == 2 ? '部分正确' : '错误')}})
								</span>
							</div>
              <div class="questionImageClass" v-if="question.topicImages" v-for="(image, imageIndex) in question.topicImages">
                <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
                <img :src="image.imageBase64Content" style="height:50%;width:50%;">
              </div>
							<div v-if="question.questionResult.isRight == 1">
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? activeBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
							<div v-else>
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? errorBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
						</div>
						<div style="font-size: 14px;padding:10px 15px;font-weight: bold;">
							<span>(本题得分:{{question.questionResult.score}}分) </span>
              <span style="color:green">&nbsp;&nbsp;&nbsp;&nbsp;修改得分：</span>
              <!-- 填空题 可修改分数 -->
              <el-input v-model="question.questionResult.score" size="mini" placeholder="请输入分数" type="number" :min="0" :max="question.score" style="width: 100px"></el-input>分
				      <el-button type="warning" size="mini" @click="updateObjectiveScore(question.questionResult);">保存</el-button>
						</div>
					</div>
					<p></p>
				</div>
			</div>
		</el-dialog>

		<!--主观题批改界面-->
		<el-dialog title="实验思考主观题" :visible.sync="editExpreVisible" :close-on-click-modal="false" v-loading = "eldialogscore">
			<div>
				<p style="text-align: center;">
					<span>班级：</span><span class="textspan">{{filters.classname}}</span>&nbsp;&nbsp;&nbsp;
					<span>姓名：</span><span class="textspan">{{editForm.name}}</span>&nbsp;&nbsp;&nbsp;
					<!-- <span>得分:</span><span class="textspan">{{editForm.score}}</span> -->
				</p>
				<div class="title">实验思考</div>
				<div v-for = "(item, index) in stuExpreport">
					<div class="titletopic">
						{{index+1}}.&nbsp;{{item.topic}}
						<span style="color: red">(总分：{{item.Allscore}}分)</span>
					</div>
          <div class="questionImageClass" v-if="item.topicImages" v-for="(image, imageIndex) in item.topicImages">
            <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
            <img :src="image.imageBase64Content" style="height:50%;width:50%;">
          </div>
          <div class="questionImageClass" v-if="item.topicImages" v-for="(image, imageIndex) in item.answerImages">
            <span>标准答案 [图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
            <img :src="image.imageBase64Content" style="height:50%;width:50%;">
          </div>
					<div class="draw_frame"><!-- 画图主框架区域 -->
						<!-- canvas 画图绘制区域 【图层：最上层】【动态生成】 -->
						<div class="draw_html_origin"><!-- 原始文本区域 【图层：最底层】【截图来源】-->
							<div v-html="item.content" style="padding-left: 10px;"></div>
						</div><!-- 原始文本区域 end -->
						<div class="draw_html_edited"><!-- 数据库编辑快照 【图层：中间层】【仅显示】-->
							<img v-if="item.mark" :src="item.mark" @load="draw_html_edited_load($event);"/><!-- 宽高 适应原始文本区域宽高 -->
						</div><!-- 数据库编辑快照 end -->
						<div class="draw_control"><!-- 画图控制区域 -->
							<div class="draw_operate"><!-- 画图操作区域 -->
								<button id="draw_correct_btn" class="draw_color_btn" @click="draw_correct_btn_click($event, index, item.id);">批改</button>
								<button id="draw_save_btn" class="draw_color_btn" @click="draw_save_btn_click($event, index, item.id);">保存</button>
								<button id="draw_export_btn" class="draw_color_btn">导出</button>
							<button id="draw_back_btn" class="draw_color_btn">后退</button>
								<button id="draw_forward_btn" class="draw_color_btn">前进</button>
								<button id="draw_restore_btn" class="draw_color_btn">恢复</button>
							</div><!-- 画图操作区域 end -->
						</div><!-- 画图控制区域 end -->
					</div><!-- 画图主框架区域 end -->
					<span style="padding-left: 20px;">请输入分数：</span>
					<el-input v-model="item.score" placeholder="请输入分数" type="number" :min="0" :max="item.Allscore" style="width: 300px"></el-input>
					<el-button type="warning" @click="submitscore(index,item.Allscore)">保存</el-button>
				</div>
				<div class="title" style="margin-top: 20px">操作得分</div>
				<span style="padding-left: 20px;">请输入分数：</span>
				<el-input v-model="editForm.operationscore" placeholder="请输入分数" type="number" :min="0" :max="100" style="width: 300px"></el-input>
				<el-button type="warning" @click="updateoperationscore">保存</el-button>
			</div>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editExpreVisible = false">关闭</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="新增" v-model="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="姓名" prop="name">
					<el-input v-model="addForm.name" auto-complete="off"></el-input>
				</el-form-item>
				<el-form-item label="性别">
					<el-radio-group v-model="addForm.sex">
						<el-radio class="radio" :label="1">男</el-radio>
						<el-radio class="radio" :label="0">女</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="年龄">
					<el-input-number v-model="addForm.age" :min="0" :max="200"></el-input-number>
				</el-form-item>
				<el-form-item label="生日">
					<el-date-picker type="date" placeholder="选择日期" v-model="addForm.birth"></el-date-picker>
				</el-form-item>
				<el-form-item label="地址">
					<el-input type="textarea" v-model="addForm.addr"></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="addFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
			</div>
		</el-dialog>
	</section>
</template>
<script>
import util from "../../common/js/util";
//import NProgress from 'nprogress'
import {
  getUserListPage,
  removeUser,
  batchRemoveUser,
  editUser,
  addUser,
  getClassInfo,
  getExperimentnameInfo,
  getStudentAllInfo,
  getStuExreportAll,
  updatescore,
  htmltowordurl,
  updateopretion,
  reStuQuestion,
  reStuReport,
  searchTerminfo,
  getSearchTermClass,
  queryCorrectMark,
  saveCorrectMark,
  queryObjectiveQuestionResult,
  updateObjectiveQuestionResultScore,
} from "../../api/api";

// 引入在线批改(onlineCorrection)样式文件及相关js库
import "../../components/onlineCorrect/css/onlineCorrection.css"; // 自定义 css
import {
  myCanvasManagerInit,
  getCanvasDataUrl,
  setCanvasDataUrl,
  draw_correct_btn_click,
  draw_html_edited_load,
} from "onlineCorrection"; // 自定义 js 函数库
import vdom2pdf from "../../utils/vdom2pdf";
export default {
  data() {
    return {
      filters: {
        termname: "",
        classname: "",
        exid: "",
      },
      users: [],
      total: 0,
      page: 1,
      listLoading: false,
      sels: [], //列表选中列

      editFormVisible: false, //编辑界面是否显示
      editExpreVisible: false, //编辑分数页面是否显示
      editLoading: false,
      editFormRules: {
        name: [{ required: true, message: "请输入姓名", trigger: "blur" }],
      },
      //编辑界面数据
      editForm: {
        name: "", //姓名
        score: "", //分数
      },

      addFormVisible: false, //新增界面是否显示
      addLoading: false,
      addFormRules: {
        name: [{ required: true, message: "请输入姓名", trigger: "blur" }],
      },
      //新增界面数据
      addForm: {
        name: "",
        sex: -1,
        age: 0,
        birth: "",
        addr: "",
      },
      options: [], //实验信息
      classoption: [], //班级信息
      stuExprement: [], //实验目的
      stuExpreport: [], //实验报告
      report: [], //主观题分数
      eldialogscore: false, //加载
      htmlTitle: "",
      ExprementName: "", //当前实验的名称
      downloadFlag: false, //下载显示
      termoption: [], //学期信息
      activeClass: "flagBlue", //正确
      errorClass: "flagRed", //错误
      activeBorder: "BorderBlue", //正确
      errorBorder: "BorderRed", //错误
      ObjectiveQuestions: [], // 客观题题集
      num: ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"], //字母
      objectiveCorrectFormVisible: false, // 客观题批改界面
      currentStuentName: '', // 当前学生姓名
    };
  },
  methods: {
    //性别显示转换
    formatSex: function (row, column) {
      return row.sex == 1 ? "男" : row.sex == 0 ? "女" : "未知";
    },
    handleCurrentChange(val) {
      this.page = val;
      this.getUsers();
    },
    //获取用户列表
    getUsers() {
      // this.listLoading = true;
      let param = {
        classname: [], //班级名次数组
        exid: "", //实验ID
      };
      if (
        this.filters.termname === "" ||
        this.filters.classname === "" ||
        this.filters.exid === ""
      ) {
        this.$message.error("请选择");
        return;
      }

      param.exid = this.filters.exid;

      param.classname.push(this.filters.classname);

      if (this.filters.classname === "-") {
        param.classname = [];
        for (let i = 0; i < this.classoption.length; i++) {
          if (this.classoption[i].value !== "-") {
            param.classname.push(this.classoption[i].value);
          }
        }
      }

      getStudentAllInfo(param).then((res) => {
        if (res.data.code == 200) {
          this.users = res.data.data;
          this.downloadFlag = true;
        } else {
          this.downloadFlag = false;
          this.$message({
            message: res.data.msg,
            type: "warning",
          });
          this.users = [];
        }
        this.listLoading = false;
      });
    },
    //删除
    handleDel: function (index, row) {
      this.$confirm("确认删除该记录吗?", "提示", {
        type: "warning",
      })
        .then(() => {
          this.listLoading = true;
          //NProgress.start();
          let para = { id: row.id };
          removeUser(para).then((res) => {
            this.listLoading = false;
            //NProgress.done();
            this.$message({
              message: "删除成功",
              type: "success",
            });
            this.getUsers();
          });
        })
        .catch(() => {});
    },
    //显示编辑界面
    handleEdit: function (index, row) {
      this.editFormVisible = true;
      this.editForm = Object.assign({}, row);
      let param = {
        exid: row.exid,
        stuid: row.id,
      };
      this.htmlTitle += "-" + row.name + "实验报告";
      getStuExreportAll(param).then((res) => {
        this.stuExprement = Object.assign([], res.data.data);
        this.stuExpreport = Object.assign([], res.data.report);
      });
      // 查询客观题
      this.getExperimentsObjectiveQuestion(row);
      // console.log(row);
    },
    // 查询实验报告 部分 客观题（单选/多选/填空题）
    getExperimentsObjectiveQuestion(row) {
      let param = {
        exid: row.exid,
        stuid: row.id,
      };
      queryObjectiveQuestionResult(param).then((res) => {
        if (res.data.code == 200) {
          this.ObjectiveQuestions = res.data.data;
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //重置预习
    handleReExame: function (index, row) {
      let param = {
        exid: row.exid,
        stuid: row.id,
      };
      this.$confirm("此操作将重置学生预习成绩, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          reStuQuestion(param)
            .then((res) => {
              if (res.data.code === 200) {
                this.$message({
                  message: res.data.msg,
                  type: "success",
                });
                this.getUsers();
              } else {
                this.$message.error(res.data.msg);
              }
            })
            .catch((ret) => {
              this.$message.error("重置成绩失败");
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消重置",
          });
        });
    },
     //重置报告
     handleResetReport: function (index, row) {
      let param = {
        exid: row.exid,
        stuid: row.id,
      };
      this.$confirm("此操作将重置学生报告, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          reStuReport(param)
            .then((res) => {
              if (res.data.code === 200) {
                this.$message({
                  message: res.data.msg,
                  type: "success",
                });
                this.getUsers();
              } else {
                this.$message.error(res.data.msg);
              }
            })
            .catch((ret) => {
              this.$message.error("重置报告失败");
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消重置",
          });
        });
    },
    //显示分数编辑页面
    handleExprement(index, row) {
      this.editExpreVisible = true;
      this.editForm = Object.assign({}, row);
      let param = {
        exid: row.exid,
        stuid: row.id,
      };
      getStuExreportAll(param).then((res) => {
        this.stuExpreport = Object.assign([], res.data.report);
        //console.log(this.stuExpreport);

        //console.log(res.data.report);
        // 初始化 onlineCorrection js 中的 myCanvasManager
        let uniqueId = param.exid + "_" + param.stuid;
        let canvasNum = this.stuExpreport.length;
        myCanvasManagerInit(canvasNum, uniqueId);
      });
    },
    // 显示客观题批改界面
    handleExprementObjecctive(index, row) {
      this.objectiveCorrectFormVisible = true;
      this.currentStuentName = row.name;
      // 查询客观题
      this.getExperimentsObjectiveQuestion(row);
    },
    // 更新客观题分数
    updateObjectiveScore(questionResult) {
      let param = {
        objectiveQuestionId: questionResult.objectiveQuestionId,
        questionResultId: questionResult.id,
        score: questionResult.score,
      };
      updateObjectiveQuestionResultScore(param).then((res) => {
        if (res.data.code === 200) {
          questionResult.isRight = res.data.data.isRight;
          questionResult.score = res.data.data.score;
          this.getUsers();
          this.$message({
            message: res.data.msg,
            type: "success",
          });
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //显示新增界面
    handleAdd: function () {
      this.addFormVisible = true;
      this.addForm = {
        name: "",
        sex: -1,
        age: 0,
        birth: "",
        addr: "",
      };
    },
    //编辑
    editSubmit: function () {
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.$confirm("确认提交吗？", "提示", {}).then(() => {
            this.editLoading = true;
            //NProgress.start();
            let para = Object.assign({}, this.editForm);
            para.birth =
              !para.birth || para.birth == ""
                ? ""
                : util.formatDate.format(new Date(para.birth), "yyyy-MM-dd");
            editUser(para).then((res) => {
              this.editLoading = false;
              //NProgress.done();
              this.$message({
                message: "提交成功",
                type: "success",
              });
              this.$refs["editForm"].resetFields();
              this.editFormVisible = false;
              this.getUsers();
            });
          });
        }
      });
    },
    //新增
    addSubmit: function () {
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.$confirm("确认提交吗？", "提示", {}).then(() => {
            this.addLoading = true;
            //NProgress.start();
            let para = Object.assign({}, this.addForm);
            para.birth =
              !para.birth || para.birth == ""
                ? ""
                : util.formatDate.format(new Date(para.birth), "yyyy-MM-dd");
            addUser(para).then((res) => {
              this.addLoading = false;
              //NProgress.done();
              this.$message({
                message: "提交成功",
                type: "success",
              });
              this.$refs["addForm"].resetFields();
              this.addFormVisible = false;
              this.getUsers();
            });
          });
        }
      });
    },
    selsChange: function (sels) {
      this.sels = sels;
    },
    //批量删除
    batchRemove: function () {
      var ids = this.sels.map((item) => item.id).toString();
      this.$confirm("确认删除选中记录吗？", "提示", {
        type: "warning",
      })
        .then(() => {
          this.listLoading = true;
          //NProgress.start();
          let para = { ids: ids };
          batchRemoveUser(para).then((res) => {
            this.listLoading = false;
            //NProgress.done();
            this.$message({
              message: "删除成功",
              type: "success",
            });
            this.getUsers();
          });
        })
        .catch(() => {
          this.$message.error("网络故障");
        });
    },
    getClass(value) {
      this.classoption = [];
      this.filters.classname = "";
      if (value == "-") {
        getClassInfo()
          .then((res) => {
            let classInfo = Object.assign([], res.data.data);
            for (var i = 0; i < classInfo.length; i++) {
              this.classoption.push({
                value: classInfo[i].class,
                label: classInfo[i].class,
              });
            }
          })
          .catch((ret) => {
            this.$message.error("网络故障");
          });
      } else {
        let param = {
          termname: value,
        };
        getSearchTermClass(param)
          .then((res) => {
            let classInfo = Object.assign([], res.data.data);
            this.classoption.push({
              value: "-",
              label: "不限",
            });
            for (var i = 0; i < classInfo.length; i++) {
              this.classoption.push({
                value: classInfo[i].classname,
                label: classInfo[i].classname,
              });
            }
          })
          .catch((ret) => {});
      }
    },
    /**
     * getExperimentnameInfo获取实验信息
     * @param classname 班级名称
     */
    getExperimentname(classname) {
      let param = {
        termname: this.filters.termname,
        classname: classname,
      };
      // console.log(classname);
      getExperimentnameInfo(param)
        .then((res) => {
          // console.log(res.data.data);
          this.options = res.data.data;
        })
        .catch((ret) => {
          this.$message.error("网络故障");
        });
    },
    zeroPadding(num, digit) {
      var zero = "";
      for (var i = 0; i < digit; i++) {
        zero += "0";
      }
      return (zero + num).slice(-digit);
    },
    //下载实验报告
    uploadExprement() {
      const template = this.$refs.resume.innerHTML;
      let html = `<!DOCTYPE html>
				<html>
				<head>
					<title></title>
					<meta charset="utf-8">
				</head>
				<body>
					${template}
				</body>
				</html>`;
      var a = document.createElement("a");
      var url = window.URL.createObjectURL(
        new Blob([html], { type: "text/html" + ";charset=" + "utf-8" })
      );
      a.href = url;
      a.download = this.htmlTitle || "file";
      a.click();
      window.URL.revokeObjectURL(url);
    },
    //下载pdf格式的实验报告
    dowloadExperiment() {
      const vdomRef = this.$refs.resume
      vdom2pdf(this,vdomRef,this.htmlTitle || 'file')
    },
    //保存分数
    submitscore(index, Allscore) {
      if (Number(this.stuExpreport[index].score) > Number(Allscore)) {
        this.$message.error("得分不能大于题目总分！");
        return false;
      }
      updatescore(this.stuExpreport[index])
        .then((res) => {
          if (res.data.code == 200) {
            this.$message({
              message: res.data.msg,
              type: "success",
            });
            this.getUsers();
          } else {
            this.$message.error(res.data.msg);
          }
        })
        .catch((ret) => {
          this.$message.error("网络故障");
        });
    },
    // 学期改变时
    termchange(value) {
      this.filters.exid = "";
      this.getClass(value);
    },
    // 班级选择变更
    classChangeSelect(value) {
      this.filters.exid = "";
      return this.getExperimentname(value);
    },
    // 实验选择
    changeSelect(value) {
      for (var i = 0; i < this.options.length; i++) {
        if (this.options[i].id == value) {
          this.ExprementName = this.options[i].ename;
          this.htmlTitle = this.options[i].ename;
        }
      }
    },
    //修改操作得分
    updateoperationscore() {
      // console.log(this.editForm);
      if (this.editForm.operationscore > 100) {
        this.$message.error("操作得分不得大于100");
        return false;
      }
      updateopretion(this.editForm)
        .then((res) => {
          if (res.data.code == 200) {
            this.$message({
              message: res.data.msg,
              type: "success",
            });
            this.getUsers();
          } else {
            this.$message.error(res.data.msg);
          }
        })
        .catch((ret) => {
          this.$message.error("网络故障");
        });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]));
    },
    //下载学生信息
    UploadCourse() {
      // console.log(this.users);
      const { export_json_to_excel } = require("../../vendor/Export2Excel");
      const tHeader = [
        "学生学号",
        "学生姓名",
        "实验时长",
        "实验预习成绩",
        "实验操作成绩",
        "实验报告成绩",
        "总分",
      ];
      const filterVal = [
        "num",
        "name",
        "duration",
        "score",
        "operationscore",
        "reportscore",
        "rationscore",
      ];
      const list = this.users;
      const data = this.formatJson(filterVal, list);
      var classname = this.filters.classname;
      var course = "";
      for (var i = 0; i < this.options.length; i++) {
        if (this.options[i].id == this.filters.exid) {
          course = this.options[i].ename;
        }
      }
      var title = course + "—成绩总计(" + classname + ")";
      export_json_to_excel(tHeader, data, title);
    },
    //获取学期信息
    getsearchTerminfo() {
      searchTerminfo()
        .then((res) => {
          this.termoption = res.data.data;
        })
        .catch((ret) => {});
    },
    /**
     * 编辑快照加载事件
     * @param event 事件对象（默认）
     */
    draw_html_edited_load(event) {
      let thisImg = event.target; // 获取图片对象
      draw_html_edited_load($(thisImg));
    },
    /**
     * “批改”按钮点击事件
     * @param event 事件对象（默认）
     * @param index 遍历序号
     * @param stureportid 学生实验报告表(stureport)的id
     */
    draw_correct_btn_click(event, index, stureportid) {
      //console.log(index, stureportid);
      let thisBtn = event.target; // 获取事件按钮
      let param = {
        stureportid: stureportid,
      };
      // 请求数据库 stureportid 对应的 mark 批改痕迹图片
      queryCorrectMark(param).then((res) => {
        //console.log(res.data.data);
        var imgDataUrl = "";
        if (res.data.data) {
          imgDataUrl = res.data.data.mark;
        }
        // 获取 el-dialog 标签 body 对象（滚动条控制）
        var $el_dialog_body = $($(".el-dialog__wrapper").get(1));
        // 调用 onlineCorrection js 函数库 初始化 myCanvas
        draw_correct_btn_click(thisBtn, index, imgDataUrl, $el_dialog_body);
        this.$message({
          message: "批改模块加载完成",
          type: "success",
        });
      });
    },
    /**
     * 批改“保存”按钮点击事件
     * @param event 事件对象（默认）
     * @param index 遍历序号
     * @param stureportid 学生实验报告表(stureport)的id
     */
    draw_save_btn_click(event, index, stureportid) {
      // 调用 onlineCorrection js 函数库 获取 canvas 显示图片 data url
      var canvasDataUrl = getCanvasDataUrl(index);
      this.saveCorrectMark(-1, stureportid, canvasDataUrl); // 本地不保存 correctmark.id，以 -1 表示没有
    },
    /**
     * 保存批改痕迹
     *     stureportid 与 mark 一一对应
     * @param correctmarkid 批改痕迹表(correctmark)的id
     * @param stureportid 学生实验报告表(stureport)的id
     * @param mark 批改痕迹内容（图片base64格式）
     */
    saveCorrectMark(correctmarkid, stureportid, mark) {
      if (!correctmarkid || !stureportid || !mark) {
        return;
      }
      let param = {
        id: correctmarkid,
        stureportid: stureportid,
        mark: mark,
      };
      saveCorrectMark(param).then((res) => {
        //console.log(res.data.data);
        this.$message(res.data.msg);
      });
    },
  },
  mounted() {
    // this.getUsers();
    // this.getClass();
    this.getExperimentname();
    this.getsearchTerminfo();
  },
};
</script>

<style scoped>
.textspan {
  text-decoration: underline;
}
.title {
  font-weight: bold;
  font-size: 15px;
  padding: 10px 15px;
}
.content {
  padding: 10px 15px;
}
.titletopic {
  font-size: 14px;
  padding: 10px 15px;
  font-weight: bold;
}
.pselect {
  height: 40px;
  line-height: 40px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  box-sizing: border-box;
  color: #606266;
  font-weight: 500;
  font-size: 14px;
  width: 70%;
  padding-left: 20px;
}
.pselectdiv span {
  font-size: 14px;
  margin-bottom: 20px;
  color: #666;
}
.flagRed {
  color: #ff6547 !important;
}
.flagBlue {
  color: #25bb9b !important;
}
.BorderBlue {
  border-color: #25bb9b !important;
}
.BorderRed {
  border-color: #ff6547 !important;
}
.questionImageClass {
  margin-left: 3%;
  font-weight: bold;
}
.flex-center{
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>