<template>
	<section  v-loading="contentLoading" style="min-width: 758px">
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
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
			<el-table-column prop="tnum" label="账号"   >
			</el-table-column>
			<el-table-column prop="pwd" label="密码"  >
			</el-table-column>
			<el-table-column label="操作" width="150">
				 <template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.$index, scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.$index, scope.row)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>
		<!--编辑界面-->
		<el-dialog title="编辑" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="账号" prop="tnum">
					<el-input v-model="editForm.tnum" auto-complete="off" disabled></el-input>
				</el-form-item>
				<el-form-item label="密码" prop="pwd">
					<el-input v-model="editForm.pwd" auto-complete="off"></el-input>
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
				<el-form-item label="账号" prop="tnum">
					<el-input v-model="addForm.tnum" auto-complete="off"></el-input>
				</el-form-item>
				<el-form-item label="密码" prop="pwd">
					<el-input v-model="addForm.pwd" auto-complete="off"></el-input>
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
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { getAdminInfo, removeUser, batchRemoveUser, editUser, addUser ,ImportStudentInfo,getClassInfo,addAdminInfo,removeAdmin,editAdmin} from '../../api/api';

	export default {
		data() {
			return {
				contentLoading:false,
				filters: {
					num: '',//学号
					class:'',//班级
				},
				users: [],
				total: 0,//总计
				page: 1,//当前页数
				pageSize:15,//每页数据
				listLoading: false,
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					tnum : [
						{ required: true, message: '请输入账号', trigger: 'blur' }
					],
					pwd:[
						{ required: true, message: '请输入密码', trigger: 'blur' }
					],
				},
				//编辑界面数据
				editForm: {
					name: '',
					num: '',
					class:'',
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					tnum : [
						{ required: true, message: '请输入账号', trigger: 'blur' }
					],
					pwd:[
						{ required: true, message: '请输入密码', trigger: 'blur' }
					],
				},
				//新增界面数据
				addForm: {
					tunm: '',
					pwd: '',
				},
				//筛选内容
				classfilter:[],

			}
		},
		methods: {
			//性别显示转换
			formatSex: function (row, column) {
				return row.sex == 1 ? '男' : row.sex == 0 ? '女' : '未知';
			},
			handleCurrentChange(val) {
				this.page = val;
				this.getUsers();
			},
			//获取用户列表
			getUsers() {
				let para = {
				};
				this.listLoading = true;
				getAdminInfo(para).then((res) => {
					this.users = res.data.data;
					this.listLoading = false;
				});
			},
			//删除
			handleDel: function (index, row) {
				this.$confirm('确认删除该记录吗?', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					//NProgress.start();
					let para = { id: row.id };
					removeAdmin(para).then((res) => {
						this.listLoading = false;
						if(res.data.code == 200){
							this.$message({
								message: res.data.msg,
								type: 'success'
							});
							this.getUsers();
						}else{
							this.$message.error(res.data.msg);
						}
					});
				})
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
					tnum:'',
					pwd:''
				};
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.editLoading = true;
							//NProgress.start();
							let para = Object.assign({}, this.editForm);
							editAdmin(para).then((res) => {
								this.editLoading = false;
								if(res.data.code == 200){
									this.$message({
										message: res.data.msg,
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.editFormVisible = false;
									this.getUsers();
								}else{
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
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.addLoading = true;
							//NProgress.start();
							let para = Object.assign({}, this.addForm);
							addAdminInfo(para).then((res) => {
								this.addLoading = false;
								if(res.data.code == 200){
									this.$message({
										message: res.data.msg,
										type: 'success'
									});
									this.$refs['addForm'].resetFields();
									this.addFormVisible = false;
									this.getUsers();
								}else{
									this.$message.error(res.data.msg);
								}
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
				var ids = this.sels.map(item => item.id).toString();
				this.$confirm('确认删除选中记录吗？', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					//NProgress.start();
					let para = { ids: ids };
					batchRemoveUser(para).then((res) => {
						this.listLoading = false;
						if(res.data.code == 200){
							this.$message({
								message: res.data.msg,
								type: 'success'
							});
							this.getUsers();
						}else{
							this.$message.error(res.data.msg);
						}
					});
				}).catch(() => {
					this.$message.error("网络故障");
				});
			},
			//beforeRemove()文件上传
			beforeUpload(file){
				var arr = file.name.split(".");
				if(arr[1] == 'xls' || arr[1] == 'xlsx'){
					let fileDate = new FormData();
					fileDate.append('file',file);
					this.contentLoading = true;
					ImportStudentInfo(fileDate).then(res => {
						this.contentLoading = false;
						if(res.data.code == 200){
							this.$message({
					          message: res.data.msg,
					          type: 'success'
					        });
					        this.getUsers();
						}else{
							this.$message.error(res.data.msg);
						}
					}).catch(ret => {
						this.contentLoading = false;
						this.$message.error('好像断网了，请检查！');
					})
				}else{
					this.$message.error('只能上传xls和xlsx文件，请检查！');
				}
				return false;
			},
			//获取班级信息
			getstudentclass(){
				getClassInfo().then(res => {
					// console.log(res.data);
					var data = Object.assign([],res.data.data);
					var classfilter = [];
					for(var i = 0 ;i<data.length;i++){
						let filters = {
							text : data[i].class,
							value :data[i].class
						}
						classfilter[i] = filters;
					}
					this.classfilter = classfilter;
				}).catch(ret => {
					this.$message.error('好像断网了，请检查！');
				})
			},
			selectClass(value){
				this.getUsers();
			}
		},
		mounted() {
			this.getUsers();
		}
	}

</script>

<style scoped>

</style>