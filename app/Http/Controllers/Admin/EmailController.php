<?php

namespace App\Http\Controllers\Admin;
use App\Model\Backlink;
use App\Model\BacklinkReturn;
use Mail;
use App\Mail\MailClass;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\EmailRequest;

class EmailController extends BaseController
{

    public function SendMail(EmailRequest $req,$id) {
        if (! \Auth::guard('admin')->user()->can('email_send_message')) {
            return redirect()->route('home');
        }
        $msg=Backlink::find($id);
        $msg->update(['status'=>1]);
        $msg->text=$req->text;
        $msg->subject=$req->subject;
        
        $addInBD= new BacklinkReturn;
        $addInBD->idBacklink=$id;
        $addInBD->title=$req->subject;
        $addInBD->text=$req->text;
        $addInBD->save();
        Mail::to($msg->email)->send(new MailClass($msg));
        return redirect('/admin/email')->with('success', 'Сообщение отправлено'); 
    }


    public function emails(){
        if (! \Auth::guard('admin')->user()->can('email_view')) {
            return redirect()->route('home');
        }

        return view('Admin.email.index',['AllEmail'=>Backlink::orderBy('id', 'DESC')->paginate(15)]);

    }

    public function lookemail($id)
    {
        if (! \Auth::guard('admin')->user()->can('email_send_message')) {
            return redirect()->route('home');
        }
        $looks=Backlink::where('id',$id)->first();
        $looks->status=!$looks->status;
        $looks->save();
        return redirect('/admin/email');
    }

    public function showOneEmail(Request $req,$id){
        if (! \Auth::guard('admin')->user()->can('email_view')) {
            return redirect()->route('home');
        }
            $SelectedEmail=Backlink::find($id);
            $SelectedEmail->subject=$this->getOption('defaultEmailSubject');
            $AllReturn=BacklinkReturn::where('idBacklink',$id)->get();
            return view('Admin.email.show',compact('SelectedEmail','AllReturn'));
    }

}
