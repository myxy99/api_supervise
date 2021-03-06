<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Model\Position;

/**
 * @method static create(array $array)
 */
class User extends \Illuminate\Foundation\Auth\User implements JWTSubject, Authenticatable
{
    /**
     * 定义模型关联的数据表
     * @var string
     */
    protected $table = 'users';
    /**
     * 定义主键
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 定义禁止操作时间
     * @var bool
     */
    public $timestamps = true;
    /**
     * @var null
     */
    protected $rememberTokenName = NULL;

    /**
     * 设置批量赋值
     *
     * @var array
     */
    protected $guarded = [];


    /**
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return self::getKey();
    }

    /**
     * 根据用户id获取用户信息
     * @param $UserId
     * @param array $array
     * @return mixed
     * @throws \Exception
     */
    public static function getUserInfo($UserId, $array = [])
    {
        try {
            return $array == null ?
                self::where('id', $UserId)->get() :
                self::where('id', $UserId)->get($array);
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('查询用户信息失败!', [$e->getMessage()]);
            return null;
        }
    }


    /**
     * 创建用户
     *
     * @param array $array
     * @return |null
     * @throws \Exception
     */
    public static function createUser($array = [])
    {
        try {
            return self::create($array) ?
                true :
                false;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('添加用户失败!', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 定义与project_members的关联
     */
    public function projectMembers()
    {
        return $this->hasOne('App\ProjectMember', 'user_id', 'id');
    }


    /**
     * 获取所有人员
     *
     * @return
     * @throws \Exception
     */
    public static function getAllUsers($id, $pid)
    {
        try {
            $res = DB::table('users as t1')
                ->leftjoin('project_members as t2', 't1.id', '=', 't2.user_id')
                ->leftjoin('projects as t3', 't2.project_id', 't3.id')
                ->leftjoin('positions as t4', 't1.id', 't4.user_id')
                ->select('t1.id', 't1.name', 't2.type', 't4.position_code', 't1.phone_number', 't1.email', 't3.name as project_name')
                ->where('t3.amdin_user_id', $id)
                ->where('t1.access_code', '0')
                ->where('t3.id', $pid)
                ->paginate(env('PAGE_NUM'));
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('获取所有人员失败!', [$e->getMessage()]);
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getUpdateUsers($id)
    {
        try {
            $data = DB::table('users as t1')
                ->leftJoin('positions as t2', 't1.id', 't2.user_id')
                ->select('t1.id', 't1.name', 't1.phone_number', 't1.email', 't2.position_code')
                ->where('t1.id', $id)
                ->get();
            return $data;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('获取修改人员失败!', [$e->getMessage()]);
        }
    }

    /**
     *获取要修改人员
     * */
    public static function getUpdateUser($id)
    {
        try {
            $user = User::find($id);
            if ($user != null) {
                $message = $user->name . '被删除了';
                \App\Utils\Logs::logInfo($message, Auth::user());
                return $user->delete();
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('删除用户失败！', [$e->getMessage()]);
            return null;
        }
    }

    /**
     *修改人员
     */
    public static function updateUser($request, $id)
    {
        try {
            $res = DB::table('users as t1')
                ->join('project_members as t2', 't1.id', 't2.user_id')
                ->join('projects as t3', 't2.project_id', 't3.id')
                ->join('positions as t4', 't4.user_id', 't1.id')
                ->where('t1.id', $id)
                ->where('t3.id', $request->pid)
                ->update([
                    't2.type' => $request->pcode
                ]);
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('修改人员失败!', [$e->getMessage()]);
        }
    }

    /**
     *移除人员
     */
    public static function deleteUser($pid, $id)
    {
        try {
            $res = DB::table('project_members')
                ->where('project_id', $pid)
                ->where('user_id', $id)
                ->delete();
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('移除人员失败!', [$e->getMessage()]);
        }
    }

    /**
     *查询人员(根据传入数据的不同查出不同的数据)
     */
    public static function getUsers($data)
    {
        try {
            $res = DB::table('users as t1')
                ->join('project_members as t2', 't1.id', '=', 't2.user_id')
                ->join('projects as t3', 't2.project_id', 't3.id')
                ->join('positions as t4', 't1.id', 't4.user_id')
                ->select('t1.id', 't1.name', 't2.type', 't4.position_code', 't1.phone_number', 't1.email', 't3.name')
                ->where('t4.position_code', $data['pcode'])
                ->where('t3.id', $data['pid'])
                ->paginate(env('PAGE_NUM'));
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('获取人员失败!', [$e->getMessage()]);
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public static function searchUser($data)
    {
        try {
            $res = DB::table('users as t1')
                ->leftJoin('project_members as t2', 't1.id', '=', 't2.user_id')
                ->leftJoin('projects as t3', 't2.project_id', 't3.id')
                ->join('positions as t4', 't1.id', 't4.user_id')
                ->select('t1.id', 't1.name', 't2.type', 't4.position_code', 't1.phone_number', 't1.email', 't3.name')
                ->where('t1.name', 'like', '%' . $data . '%')
                ->orwhere('t1.email', 'like', '%' . $data . '%')
                ->orwhere('t1.phone_number', 'like', '%' . $data . '%')
                ->paginate(env('PAGE_NUM'))
                ->toarray();
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('搜索失败!', [$e->getMessage()]);
            return null;
        }
    }

    /**
     * 修改用户密码
     * @param $request
     * @return bool
     * @throws \Exception
     */
    public static function updateUserPassword($request)
    {
        try {
            if (self::checkOldPassword($request->old_password)) {
                return self::where('id', Auth::id())->update(['password' => bcrypt($request->new_password)]) ?
                    true :
                    false;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('修改密码发生错误!', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 检查用户原密码
     * @param $old_password
     * @return mixed
     * @throws \Exception
     */
    protected static function checkOldPassword($old_password)
    {
        try {
            return Hash::check($old_password, self::where('id', Auth::id())->first()->password);
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('检查用户原密码发生错误!', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * @param $str
     * @return bool
     * @throws \Exception
     */
    public static function queryUsers($str)
    {
        try {
            $data = self::where('name', 'like', '%' . $str . '%')
                ->orwhere('phone_number', 'like', '%' . $str . '%')
                ->orwhere('email', 'like', '%' . $str . '%')->paginate(env('PAGE_NUM'));
            return $data;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('查询用户失败!', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function selectUser($id)
    {
        try {
            $datas = ProjectMember::where('project_id', $id)->select('user_id')->get()->toArray();
            foreach ($datas as $k => $data) {
                $array[$k] = $data['user_id'];
            }
            $user = self::whereNotIn('id', $array)->orderBy('id', 'desc')->where('state', 1)->paginate(env('PAGE_NUM'));
            return $user;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('添加用户失败!', [$e->getMessage()]);
            return false;
        }
    }


    /**
     * @param $access_code
     * @param $page
     * @param array $array
     * @return |null
     * @throws \Exception
     */
    public static function getInfo($access_code, $page, $array = [])
    {
        try {
            if ($access_code == null) {
                $data = User::select($array)
                    ->where('access_code', '!=', '1')
                    ->leftjoin('positions as p', 'users.id', '=', 'p.user_id')
                    ->paginate($page);
            } else {
                $data = User::select($array)
                    ->where('access_code', '!=', '1')
                    ->where('p.position_code', $access_code)
                    ->leftjoin('positions as p', 'users.id', '=', 'p.user_id')
                    ->paginate($page);
            }
            return $data;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('查询用户信息失败!', [$e->getMessage()]);
            return null;
        }
    }

    /**
     * 删除用户
     * @param $user_id
     * @return |null
     * @throws \Exception
     */
    public static function adminDeleteUser($user_id)
    {
        try {
            $user = User::find($user_id);
            if ($user != null) {
                $message = $user->name . '被删除了';
                \App\Utils\Logs::logInfo($message, Auth::user());
                Position::where('user_id', $user_id)->delete();
                return $user->delete();
            } elseif ($user->access_code == '1') {
                return null;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('删除用户失败！', [$e->getMessage()]);
            return null;
        }
    }


    /**
     * 搜索用户
     * @param $search
     * @param $page
     * @param array $array
     * @return |null
     * @throws \Exception
     */
    public static function Search($search, $page, $array = [])
    {
        try {
            $data = User::select('users.id')
                ->where('name', 'like', '%' . $search . '%')
                ->orwhere('phone_number', 'like', '%' . $search . '%')
                ->orwhere('email', 'like', '%' . $search . '%')
                ->leftjoin('positions as p', 'users.id', '=', 'p.user_id')
                ->get()->toArray();
            $id[] = array();
            for ($i = 0; $i < sizeof($data); $i++) {
                $id[$i] = $data[$i]['id'];
            }
            return User::select($array)
                ->where('access_code', '!=', '1')
                ->whereIn('users.id', $id)
                ->leftjoin('positions as p', 'users.id', '=', 'p.user_id')
                ->paginate($page);
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('搜索用户失败！', [$e->getMessage()]);
            return null;
        }
    }

    /**
     * 获取指定用户信息
     * @param $user_id
     * @param array $array
     * @return |null
     * @throws \Exception
     */
    public static function ShowUserInfo($user_id, $array = [])
    {
        try {
            return User::select($array)
                ->leftjoin('positions as p', 'users.id', '=', 'p.user_id')
                ->where('users.id', '=', $user_id)->get();
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('获取用户信息失败！', [$e->getMessage()]);
            return null;
        }
    }


    /**
     * 修改用户
     * @param array $array
     * @return bool
     * @throws \Exception
     */
    public static function UpdateUserInfo($array = [])
    {
        try {
            if ($array[2] == null) {
                $user = User::where('id', $array[0])->update([
                    'name' => $array[1],
                    'phone_number' => $array[3],
                    'email' => $array[4],
                    'state' => $array[6],
                ]);
            } else {
                $user = User::where('id', $array[0])->update([
                    'name' => $array[1],
                    'password' => bcrypt($array[2]),
                    'phone_number' => $array[3],
                    'email' => $array[4],
                    'state' => $array[6],
                ]);
            }
            if ($user) {
                $temp = Position::where('user_id', '=', $array[0]);
                $res = $temp->first();
                if ($res == null) {
                    $model = new Position();
                    $model->user_id = $array[0];
                    $model->position_code = $array[5];
                    $model->save();
                } else {
                    $temp->update(['position_code' => $array[5]]);
                }
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('修改用户信息失败！', [$e->getMessage()]);
            return false;
        }
    }


    /**
     * 添加用户
     * @param array $array
     * @return bool
     * @throws \Exception
     */
    public static function AddUser($array = [])
    {
        try {
            $model = new User();
            $model->name = $array[0];
            $model->password = bcrypt($array[1]);
            $model->phone_number = $array[2];
            $model->email = $array[3];
            $result = $model->save();
            if ($result) {
                $id = User::select('id')->where('email', $array[3])->first()->id;
                $model = new Position();
                $model->user_id = $id;
                $model->position_code = $array[4];
                $model->save();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('新增用户信息失败！', [$e->getMessage()]);
            return false;
        }
    }

    public static function SetManage($id, $code)
    {
        try {
            $user = User::find($id);
            if ($user == null) {
                return -1;
            }
            if ($user->access_code == '1') {
                return -2;
            }
            $user_code = $user->update(['access_code' => $code]);
            if ($user_code) {
                return 1;
            } else {
                return -3;
            }
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('修改项目管理员失败失败！', [$e->getMessage()]);
            return false;
        }

    }


    /**
     * @param $id
     * @param $pid
     * @return |null
     * @throws \Exception
     */
    public static function setBackManager($id, $pid)
    {
        try {
            $res = DB::table('users as t1')
                ->leftjoin('project_members as t2', 't1.id', 't2.user_id')
                ->leftjoin('projects as t3', 't3.id', 't2.project_id')
                ->where('t1.id', $id)
                ->where('t3.id', $pid)
                ->update([
                    't2.type' => 1,
                ]);
            return $res;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('异常:设置失败!', [$e->getMessage()]);
            return null;
        }
    }

}

