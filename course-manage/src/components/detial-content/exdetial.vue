<template>
	<div class="main">
		<div class="course-head">
			<el-breadcrumb separator="/" class="breadcrumb-inner">
				<el-breadcrumb-item v-for="item in $route.matched" :key="item.path" >
					{{ item.name }}
				</el-breadcrumb-item>
			</el-breadcrumb>
			<h3>{{course.coname}}</h3>
			<div class="course-tag">
				<a href="javascript:;">开始实验 | 尚未完结</a>
				<div class="course-tag-flag"></div>
				<span>
					<p>实验人数</p>
					<p>暂无</p>
				</span>
				<span>
					<p>实验难度</p>
					<p>中等</p>
				</span>
				<span>
					<p>实验时长</p>
					<p>课表</p>
				</span>
			</div>
		</div>
		<div class="coures-content">
			<el-tabs v-model="activeName" @tab-click="handleClick">
			    <el-tab-pane label="课程介绍" name="first">
			    	<p class="describe">{{course.cinfo}}</p>
			    	<p class="teacher">实验老师信息</p>
			    	<p>{{course.tname}}</p>
			    	<p>江苏大学 {{course.tcollege}}</p>
			    </el-tab-pane>
			    <el-tab-pane label="实验目录" name="second">
			    	<div class="excontent">
				    	<div class="exchapter" @click.stop.prevent="expand(1)">
				    		<div class="exchapter_left">
				    			实验目录
				    		</div>
				    		<div class="exchapter_right">
				    			<i class="el-icon-arrow-down" v-show="expandindex[1]"></i>
				    			<i class="el-icon-arrow-up" v-show="!expandindex[1]"></i>
				    		</div>
				    	</div>
				    	<el-collapse-transition>
					    	<div v-show="!expandindex[1]">
						    	<div v-for="(item1,index1) in data">
						    		<div style="height: 5px"></div>
						    		<a href="javascript:;" @click="rouerto(item1.id,item1.isclass,item1)">
						    		<div class="exsection">
							    		<span class="exsection_left">{{1}}.{{index1+1}}  {{item1.ename}}</span>
							    		<span class="exsection_right" :class="item1.isclass == 0 ? info : danger ">
							    			{{item1.isclass == 0 ? '未开始':'已开始'}}
							    		</span>
						    		</div>
						    		</a>
						    		<div style="height: 5px"></div>
						    	</div>
					    	</div>
				    	</el-collapse-transition>
			    	</div>
			    </el-tab-pane>
			    <el-tab-pane label="已签到" name="third">
			    	<el-table :data="exprementInfo" highlight-current-row style="width: 100%;">
						<el-table-column type="index" width="50">
						</el-table-column>
						<el-table-column prop="ename" label="实验名称" width="180" show-overflow-tooltip align="center"></el-table-column>
						<el-table-column prop="stime" label="签到时间" width="180" show-overflow-tooltip align="center">
						</el-table-column>
						<el-table-column prop="etime" label="签退时间"  width="180" show-overflow-tooltip align="center">
						</el-table-column>
						<el-table-column prop="duration" label="实验时长" width="120" show-overflow-tooltip align="center">
						</el-table-column>
						<el-table-column prop="score" label="实验预习" width="120" show-overflow-tooltip  align="center" sortable>
							<template slot-scope="scope">
								{{scope.row.score}}
							</template>
						</el-table-column>
						<el-table-column prop="operationscore" label="实验操作" width="130" show-overflow-tooltip  align="center" sortable>
							<template slot-scope="scope">
								{{scope.row.operationscore}}
							</template>
						</el-table-column>
						<el-table-column prop="reportscore" label="实验报告" width="130" show-overflow-tooltip  align="center" sortable>
							<template slot-scope="scope">
								{{scope.row.reportscore}}
							</template>
						</el-table-column>
						<el-table-column prop="rationscore" label="总分" width="110" show-overflow-tooltip  align="center" sortable>
							<template slot-scope="scope">
								{{scope.row.rationscore}}
							</template>
						</el-table-column>
						<el-table-column label="操作" width="150">
							 <template slot-scope="scope">
								<el-button type="danger" size="small" @click="handleEdit(scope.$index, scope.row)">查看</el-button>
							</template>
						</el-table-column>
					</el-table>
			    </el-tab-pane>
			</el-tabs>
		</div>
		<!--编辑界面-->
		<el-dialog title="实验报告" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<div id="pdfDom"  ref="resume">
				<p style="text-align: center;line-height: 50px;font-size: 20px;font-weight: bold">{{editForm.ename}}</p>
				<p style="text-align: center;line-height: 50px;">
					<span>班级：</span><span style="text-decoration:underline;">&nbsp;{{user.class}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
					<span>姓名：</span><span  style="text-decoration:underline;">&nbsp;{{user.name}}&nbsp;</span>&nbsp;&nbsp;&nbsp;
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
							<span>(本题得分:{{question.questionResult.score}}分)</span>
						</div>
					</div>
					<p></p>
				</div>
				<div style="font-weight: bold;font-size: 15px;padding:10px 15px;">实验思考</div>
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
				<el-button type="primary" @click.native="uploadExprement()" :loading="editLoading">下载</el-button>
			</div>
		</el-dialog>
	</div>
</template>
<script>
import {
  getChpaterExcement,
  getStuRecord,
  getStuExreportAll,
  queryObjectiveQuestionResult,
} from "@/api/api";
export default {
  name: "exdetial",
  data() {
    return {
      activeName: "first",
      expandindex: [],
      data: [],
      danger: "danger",
      info: "info",
      course: {}, //课程
      user: {}, //用户信息
      exprementInfo: [], //实验信息
      stuExprement: [], //实验报告
      stuExpreport: [], //实验答案
      htmlTitle: "", //实验报告题目
      editFormVisible: false,
      editForm: {},
      editLoading: false,
      activeClass: "flagBlue", //正确
      errorClass: "flagRed", //错误
      activeBorder: "BorderBlue", //正确
      errorBorder: "BorderRed", //错误
      ObjectiveQuestions: [], // 客观题题集
      num: ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"], //字母
    };
  },
  methods: {
    handleClick(tab, event) {
      // console.log(tab, event);
    },
    expand(index) {
      this.expandindex[index] = !this.expandindex[index];
      this.$set(this.expandindex, index, this.expandindex[index]);
    },
    getChapers() {
      let params = {
        coid: this.$route.params.num,
        class: this.user.class,
      };
      getChpaterExcement(params).then((res) => {
        // console.log(res.data.course);
        this.course = res.data.course;
        this.data = res.data.data;
        // console.log(this.data);
      });
      // console.log(this.$route.params.num);
    },
    //跳转
    rouerto(id, isclass, item) {
      if (isclass == 0) {
        this.$message.error("此实验室尚未开启，请耐心等待！");
      } else {
        this.$router.push({
          name: "开始试验",
          params: {
            id: id,
          },
        });
      }
    },
    getStuRecords() {
      let params = {
        stuid: this.user.id,
        coid: this.$route.params.num,
      };
      getStuRecord(params).then((res) => {
        // console.log(res.data.data);
        for (let i in res.data.data) {
          this.exprementInfo.push(res.data.data[i]);
        }
      });
    },
    //查看实验报告
    handleEdit(index, row) {
      let param = {
        exid: row.exid,
        stuid: this.user.id,
      };
      this.htmlTitle += "-" + row.ename + "实验报告";
      getStuExreportAll(param).then((res) => {
        this.stuExprement = Object.assign([], res.data.data);
        this.stuExpreport = Object.assign([], res.data.report);
      });
      // 查询客观题
      this.getExperimentsObjectiveQuestion(row);
      this.editFormVisible = true;
      this.editForm = Object.assign({}, row);
    },
    // 查询实验报告 部分 客观题（单选/多选/填空题）
    getExperimentsObjectiveQuestion(row) {
      let param = {
        exid: row.exid,
        stuid: this.user.id,
      };
      queryObjectiveQuestionResult(param).then((res) => {
        if (res.data.code == 200) {
          this.ObjectiveQuestions = res.data.data;
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //下载uploadExprement
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
  },
  mounted() {
    var user = sessionStorage.getItem("user");
    if (user) {
      this.user = JSON.parse(user);
    } else {
      this.$router.push({
        name: "login",
      });
    }
    this.getChapers();
    this.getStuRecords();
  },
};
</script>
<style scoped>
.main {
  width: 90%;
  margin: 0 auto;
  margin-top: 30px;
}
.course-head {
  background: url("http://img.mukewang.com/55af49ad000116a506000338.jpg")
    center/cover no-repeat;
  color: #fff;
  margin-bottom: 30px;
  overflow: hidden;
  height: 220px;
  padding: 0 5%;
}
.breadcrumb-inner {
  height: 40px;
  line-height: 40px;
  font-size: 15px;
}
.course-head h3 {
  font-weight: bold;
  color: white;
  font-size: 24px;
}
.course-tag {
  position: relative;
}
.course-tag-flag {
  width: 220px;
  display: inline-block;
}
.course-tag a {
  background-color: #f01414;
  color: #fff;
  font-size: 14px;
  text-decoration: none;
  display: inline-block;
  height: 42px;
  width: 183px;
  line-height: 42px;
  text-align: center;
  position: absolute;
  top: 20px;
}
.course-tag span {
  display: inline-block;
  font-size: 14px;
  width: 120px;
}
.describe {
  font-size: 16px;
  line-height: 30px;
  text-indent: 2em;
  color: #333;
}
.teacher {
  font-size: 24px;
  color: #333;
  margin: 0px;
}
.coures-content {
  padding-bottom: 100px;
}
.exchapter {
  height: 50px;
  line-height: 50px;
  border-bottom: 1px solid #ddd;
  color: #333;
  cursor: pointer;
  font-size: 15px;
}
.exchapter_left {
  float: left;
}
.exchapter_right {
  float: right;
  width: 50px;
  font-size: 20px;
}
.exsection {
  padding: 5px 15px 5px 5px;
  height: 30px;
  line-height: 30px;
  background-color: #f3f5f7;
  font-size: 14px;
  color: #787d82;
  width: 95%;
}
.exsection_right {
  float: right;
}
.exsection:hover {
  background-color: #333;
  color: #fff;
  cursor: pointer;
}
.danger {
  color: #f56c6c;
}
.info {
  color: #909399;
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
</style>
<style type="text/css">
.el-breadcrumb__inner {
  color: white !important;
}
a {
  text-decoration: none;
}
.questionImageClass {
  margin-left: 3%;
  font-weight: bold;
}
</style>