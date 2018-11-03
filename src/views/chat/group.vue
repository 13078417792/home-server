<template>
    <div class="group">

        <div class="button-area">
            <Button class="btn" type="primary" size="small" @click="crateGroup">创建群组</Button>
        </div>

        <ul class="group-list">

            <li class="clear" v-for="(item,index) in group" :key="index" :style="dropdownStyle[index] || {}">
                <avatar class="avatar" :image="item.thumb"></avatar>
                <div class="content">
                    <span class="name overflow">
                        {{item.name}}
                    </span>
                </div>

                <Dropdown class="fr more" trigger="click" @on-visible-change="dropdown_change($event,index)">
                    <Button type="primary" shape="circle" icon="ios-more" size="small" ></Button>
                    <DropdownMenu slot="list" class="dropdown-menu" :style="{zIndex:11+group.length}">
                        <template v-if="item.creator_uid===currentUID">
                            <DropdownItem>修改群资料</DropdownItem>
                            <DropdownItem>解散群组</DropdownItem>
                        </template>
                        <DropdownItem v-else>退出群组</DropdownItem>
                    </DropdownMenu>
                </Dropdown>


            </li>
        </ul>
    </div>
</template>

<script>
    import createGroup from './create-group'
    import {mapGetters,mapState} from 'vuex'
    export default {
        name: "group",
        data(){
            return {
                dialog:[createGroup],
                dropdownStyle:{

                }
            }
        },
        created(){
            this.$store.dispatch('chat/getGroup')
        },
        computed:{
            ...mapState('chat',['group']),
            ...mapGetters('system',['currentUID'])
        },
        methods:{
            crateGroup(){
                this.dialogInstance['create-group'].open({
                    title:'创建群组',
                    width:'640px',
                    height:'720px'
                })
            },
            dropdown_change(status,index){
                if(status){
                    // console.log(index)
                    if(this.dropdownStyle[index]!==undefined){
                        this.dropdownStyle[index] = {zIndex:12}
                    }else{
                        this.$set(this.dropdownStyle,index,{zIndex:12})
                    }
                }else{
                    // delete this.dropdownStyle[index]
                    this.dropdownStyle[index] = null
                }
            }
        }
    }
</script>

<style scoped lang="less">

    .group{
        width:100%;

        .button-area{
            padding:.5em 0;
            background-color:#F8F8F8;
            overflow:hidden;

            .btn{
                float:right;
                margin-right:10px;
            }
        }

        .group-list{
            margin:10px 0;
            width:100%;
            user-select:none;

            @avatar-size:3em;
            li{
                position:relative;
                padding:10px;
                list-style:none;
                z-index:10;

                &:hover{
                    background-color:#FAFAFA;
                    z-index:11;
                }

                .avatar{
                    width:@avatar-size;
                    height:@avatar-size;
                    float:left;
                }

                @padding-right:2em;
                .content{
                    height:@avatar-size;
                    overflow:hidden;
                    padding:0 @padding-right 0 1em;
                    position:relative;
                    z-index:1;

                    @group-name-height:2em;
                    @group-name-fontdize:.9em;
                    .name{
                        max-width:100%;
                        display:inline-block;
                        font-size:@group-name-fontdize;
                        height:@group-name-height/@group-name-fontdize;
                        line-height:@group-name-height/@group-name-fontdize;

                    }

                }

                .more{
                    position:absolute;
                    top:5px;
                    right:5px;
                    transform:translate(-50%,50%);
                    z-index:30;
                }




            }
        }
    }
</style>

<style lang="less">

    /*.group-list{

        li{

            .more{

                .ivu-select-dropdown{
                    z-index:15;
                }
            }
        }
    }*/
</style>