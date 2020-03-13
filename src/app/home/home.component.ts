import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

import { HttpService } from '../_lib_service/index_helper';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: [
      './home.component.scss'
    ]
})

export class HomeComponent implements OnInit {


    public forms    = {
        userName:       'aaa',
        closPlatform:   true,
        builtIn:        false
    }

    constructor(
        private readonly httpClient: HttpClient,
        private  httpService: HttpService,
    ) {
    }

    ngOnInit(): void {
        // this.setup();
    }


    public doLogin(): void
    {
        alert('aa');
    }

    public checkForm(): boolean
    {
        if (this.forms.userName.length >= 2
        ) {
            return true;
        } else {
            return false;
        }
    }



    private login() 
    {
        const url = 'https://yasukosan.dip.jp/fido_angular/server/public/api/assertion/start';
        // 入力フォームの内容を取得
        let body = new HttpParams();

        body = body.set('username', this.forms.userName);

    
        this.httpClient.post(url, body)
          .subscribe(
            response => this.handleAssertionStart(response), () => {
              this.messagesService.showErrorToast('Login failed');
            }
          );
    }
}