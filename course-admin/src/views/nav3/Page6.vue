<template>
	<div  v-loading="contentLoading" style="min-width: 758px">
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true">
				<el-form-item>
					<el-button type="primary" @click="handleAdd">新增</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="users" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="index" width="60">
			</el-table-column>
			<el-table-column prop="name" label="学期名称"   >
			</el-table-column>
			<el-table-column label="操作" width="250">
				 <template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.$index, scope.row)">编辑</el-button>
					<el-button size="small" type="primary" @click="handleAddClass(scope.$index, scope.row)">添加班级</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.$index, scope.row)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>
		<!--编辑界面-->
		<el-dialog title="编辑" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="学期名称" prop="name">
					<el-input v-model="editForm.name" auto-complete="off"></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
    	</el-dialog>

    	<!--新增界面-->
		<el-dialog title="新增" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="学期名称" prop="name">
					<el-input v-model.trim="addForm.name" auto-complete="off"></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="addFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
			</div>
		</el-dialog>

		<!-- 打开添加班级页面 -->
		<el-dialog :title="nowTermName" :visible.sync="addClassVisible">
			<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-select v-model="filters.classname" filterable placeholder="请选择班级">
				    <el-option
				      v-for="(item,index) in classoption"
				      :key="index"
				      :label="item.label"
				      :value="item.value">
				    </el-option>
				</el-select>
				<el-form-item>
					<el-button type="primary" @click="addTermClass">添加</el-button>
				</el-form-item>
			</el-form>
		</el-col>
		  <el-table :data="termClassData">
		    <el-table-column property="id" label="ID" width="50"></el-table-column>
		    <el-table-column property="termname" label="学期名称" width="200"></el-table-column>
		    <el-table-column property="classname" label="班级"></el-table-column>
		    <el-table-column label="操作">
		    	<template slot-scope="scope">
					<el-button type="primary" size="small" @click="addClassExperiments(scope.$index, scope.row)">添加实验</el-button>
					<el-button type="danger" size="small" @click="handleClassDel(scope.$index, scope.row)">删除</el-button>
				</template>
			</el-table-column>
		  </el-table>
		</el-dialog>

		<!-- 打开添加班级实验页面 -->
		<el-dialog :title="nowTermClassName" :visible.sync="addClassExperimentsVisible">
			<el-form :model="filters">
				<div>
					<el-form-item>
						<el-checkbox v-model="isclassExperimentsSelectAll" @change="classExperimentsSelectAll">全选</el-checkbox>
						<el-checkbox-group v-model="filters.experimentIds" style="width: 100%">
							<div v-for="(experiment, experimentIndex) in filters.experiments">
								<el-checkbox :label="experiment.id" style="width: 100%" border>
									{{experimentIndex+1}} 、 {{experiment.name}}
								</el-checkbox>
							</div>
						</el-checkbox-group> 
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="updateClassExperiments()">保存</el-button>
					</el-form-item>
				</div>
			</el-form>
		</el-dialog>

	</div>
</template>

<script>
import util from "../../common/js/util";
//import NProgress from 'nprogress'
import {
  addTerminfo,
  searchTerminfo,
  updateTerminfo,
  removeTerminfo,
  getClassInfo,
  addTermClass,
  getSearchTermClass,
  delTermClass,
  saveTermClassExperiments,
  queryAllExperiments,
} from "../../api/api";

export default {
  data() {
    return {
      contentLoading: false,
      filters: {
        classname: "", //班级
        experiments: [
          {
            id: 2,
            name: "实验一",
          },
          {
            id: 5,
            name: "实验二",
          },
          {
            id: 6,
            name: "实验三",
          },
          {
            id: 7,
            name: "实验四",
          },
        ], // 实验信息
        experimentIds: [2, 5, 7], // 实验Ids列表
      },
      nowTermClassName: "", // 当前学期-班级名称
      addClassExperimentsVisible: false, // 添加班级实验窗口
      isclassExperimentsSelectAll: false, // 实验是否全选
      currentTermClassInfo: "", // 当前学期班级信息
      currentTermClassInfoIndex: "", // 当前学期课程列表序号
      users: [],
      total: 0, //总计
      page: 1, //当前页数
      pageSize: 15, //每页数据
      listLoading: false,
      sels: [], //列表选中列

      editFormVisible: false, //编辑界面是否显示
      editLoading: false,
      editFormRules: {
        name: [{ required: true, message: "请输入学期名称", trigger: "blur" }],
      },
      //编辑界面数据
      editForm: {
        name: "",
      },

      addFormVisible: false, //新增界面是否显示
      addLoading: false,
      addFormRules: {
        name: [{ required: true, message: "请输入学期名称", trigger: "blur" }],
      },
      //新增界面数据
      addForm: {
        name: "",
      },
      //筛选内容
      classfilter: [],
      //新增班级页面是否显示
      addClassVisible: false,
      nowTermName: "", //学期名字
      classoption: [], //班级
      termRow: {}, //学期对象
      termClassData: [], //学期班级数据
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
      let para = {};
      this.listLoading = true;
      searchTerminfo(para).then((res) => {
        this.users = res.data.data;
        this.listLoading = false;
      });
    },
    //删除
    handleDel: function (index, row) {
      this.$confirm("确认删除该记录吗?", "提示", {
        type: "warning",
      }).then(() => {
        this.listLoading = true;
        //NProgress.start();
        let para = { id: row.id };
        removeTerminfo(para).then((res) => {
          this.listLoading = false;
          if (res.data.code == 200) {
            this.$message({
              message: res.data.msg,
              type: "success",
            });
            this.getUsers();
          } else {
            this.$message.error(res.data.msg);
          }
        });
      });
    },
    //显示编辑界面
    handleEdit: function (index, row) {
      this.editFormVisible = true;
      this.editForm = Object.assign({}, row);
    },
    //显示新增界面
    handleAdd: function () {
      this.addFormVisible = true;
      this.addForm = {
        name: "",
      };
    },
    //显示添加班级界面
    handleAddClass(index, row) {
      this.addClassVisible = true;
      this.nowTermName = row.name;
      this.termRow = row;
      this.getTermClass();
      this.classoption = [];
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
          this.$message.error("网络故障：班级信息获取失败");
        });
    },
    //编辑
    editSubmit: function () {
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.$confirm("确认提交吗？", "提示", {}).then(() => {
            this.editLoading = true;
            //NProgress.start();
            let para = Object.assign({}, this.editForm);
            updateTerminfo(para).then((res) => {
              this.editLoading = false;
              if (res.data.code == 200) {
                this.$message({
                  message: res.data.msg,
                  type: "success",
                });
                this.$refs["editForm"].resetFields();
                this.editFormVisible = false;
                this.getUsers();
              } else {
                this.$message.error(res.data.msg);
              }
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
            addTerminfo(para).then((res) => {
              this.addLoading = false;
              if (res.data.code == 200) {
                this.$message({
                  message: res.data.msg,
                  type: "success",
                });
                this.$refs["addForm"].resetFields();
                this.addFormVisible = false;
                this.getUsers();
              } else {
                this.$message.error(res.data.msg);
              }
            });
          });
        }
      });
    },
    //班级添加
    addTermClass() {
      let param = {
        termid: this.termRow.id,
        termname: this.termRow.name,
        classname: this.filters.classname,
      };
      if (this.filters.classname == "") {
        this.$message.error("请选择班级");
        return;
      }
      addTermClass(param)
        .then((res) => {
          if (res.data.code == 200) {
            this.$message({
              message: res.data.msg,
              type: "success",
            });
            this.getTermClass();
          } else {
            this.$message.error(res.data.msg);
          }
        })
        .catch((ret) => {});
    },
    //获取班级学期信息
    getTermClass() {
      let param = {
        termname: this.termRow.name,
      };
      getSearchTermClass(param)
        .then((res) => {
          if (res.data.code == 200) {
            this.termClassData = res.data.data;
          } else {
            this.$message.error(res.data.msg);
          }
        })
        .catch((ret) => {});
    },
    // 添加班级实验信息
    addClassExperiments(index, row) {
      this.currentTermClassInfoIndex = index;
      queryAllExperiments()
        .then((res) => {
          let experiments = Object.assign([], res.data.data);
          this.filters.experiments = experiments;
          this.filters.experimentIds = [];
          this.addClassExperimentsVisible = true;
          this.currentTermClassInfo = row;
          this.filters.experimentIds = row.experimentIds;
          this.isclassExperimentsSelectAll = false;
		  this.nowTermClassName = this.nowTermName + " " + row.classname;
        })
        .catch((ret) => {
          this.$message.error("网络故障：实验信息获取失败");
        });
    },
    // 实验全选
    classExperimentsSelectAll() {
      if (this.isclassExperimentsSelectAll === true) {
        let idArray = [];
        for (var i = 0; i < this.filters.experiments.length; i++) {
          idArray.push(this.filters.experiments[i].id);
        }
        this.filters.experimentIds = idArray;
      } else {
        this.filters.experimentIds = [];
      }
    },
    // 更新班级实验信息
    updateClassExperiments() {
      let param = {
        termClassId: this.currentTermClassInfo.id,
        experimentIds: this.filters.experimentIds,
      };
      saveTermClassExperiments(param).then((res) => {
        if (res.data.code == 200) {
          this.$message({
            message: res.data.msg,
            type: "success",
          });
          this.addClassExperimentsVisible = false;
		  // 更新本地缓存的学期班级中的实验数据
          this.termClassData[this.currentTermClassInfoIndex].experimentIds =
            param.experimentIds;
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //删除班级信息
    handleClassDel(index, row) {
      let param = {
        id: row.id,
      };
      this.$confirm("确认删除吗？", "提示", {}).then(() => {
        delTermClass(param)
          .then((res) => {
            if (res.data.code == 200) {
              this.$message({
                message: res.data.msg,
                type: "success",
              });
              this.getTermClass();
            } else {
              this.$message.error(res.data.msg);
            }
          })
          .catch((ret) => {});
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
            if (res.data.code == 200) {
              this.$message({
                message: res.data.msg,
                type: "success",
              });
              this.getUsers();
            } else {
              this.$message.error(res.data.msg);
            }
          });
        })
        .catch(() => {
          this.$message.error("网络故障");
        });
    },
    //beforeRemove()文件上传
    beforeUpload(file) {
      var arr = file.name.split(".");
      if (arr[1] == "xls" || arr[1] == "xlsx") {
        let fileDate = new FormData();
        fileDate.append("file", file);
        this.contentLoading = true;
        ImportStudentInfo(fileDate)
          .then((res) => {
            this.contentLoading = false;
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
            this.contentLoading = false;
            this.$message.error("好像断网了，请检查！");
          });
      } else {
        this.$message.error("只能上传xls和xlsx文件，请检查！");
      }
      return false;
    },
    //获取班级信息
    getstudentclass() {
      getClassInfo()
        .then((res) => {
          // console.log(res.data);
          var data = Object.assign([], res.data.data);
          var classfilter = [];
          for (var i = 0; i < data.length; i++) {
            let filters = {
              text: data[i].class,
              value: data[i].class,
            };
            classfilter[i] = filters;
          }
          this.classfilter = classfilter;
        })
        .catch((ret) => {
          this.$message.error("好像断网了，请检查！");
        });
    },
    selectClass(value) {
      this.getUsers();
    },
  },
  mounted() {
    this.getUsers();
  },
};
</script>

<style scoped>
.el-checkbox-width {
  width: 100%;
  height: 100%;
}
.questionClass {
  border-bottom: 1px dashed #dfe2e2;
}
</style>