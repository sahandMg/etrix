import React , {Component} from 'react';
import GoogleSingup from  '../GoogleSignup/GoogleSignup';
import { Redirect, Link } from 'react-router-dom';
import axios from 'axios';
import Validator from 'validator';
import InlineError from '../../messages/InlineError';
import { ClipLoader } from 'react-spinners';
import CardWrapper from '../../CardWrapper/CardWrapper';
import * as actions from '../../../store/actions/index';
import { connect } from 'react-redux';
import './Signup.css';
import URLs from "../../../URLs";
import Alert from 'react-s-alert';

class Signup extends Component {

    state = {
        data: {
            email: '',
            password: '',
            name: ''
        },
        // loading: false,
        errors: {}
    }

    componentDidMount() {
        this.props.checkAuthState();
        if(this.props.match.params.message != null) {
            Alert.info('برای ادامه خرید باید در سایت ثبت نام کنید', {
                position: 'bottom-right',
                effect: 'scale',
                beep: false,
                timeout: 5000,
                offset: 100
            });
        }
    }

    sendData = (event) => {
        event.preventDefault();
        const errors = this.validate(this.state.data);
        this.setState({ errors });
        if (Object.keys(errors).length === 0) {
            // this.setState({loading: true});
            console.log('sendData');
            let data = this.state.data;
            this.props.registerUser(data.email, data.password, data.name, URLs.base_URL + URLs.user_register);
            //     axios.post(URLs.base_URL+URLs.user_register,{email: this.state.data.email , password: this.state.data.password, name: this.state.data.name})
            //         .then((res) => {
            //             console.log(res);
            //             // Alert.info('برای ادامه خرید باید در سایت ثبت نام کنید', {
            //             //     position: 'bottom-right',
            //             //     effect: 'scale',
            //             //     beep: false,
            //             //     timeout: 5000,
            //             //     offset: 100
            //             // });
            //             // this.setState({loading: false});
            //         })
            //         .catch((error)=> {
            //             // this.setState({loading: false});
            //             console.log("Error");
            //             console.log(error);
            //         });
            // }
        }
    }

    validate = (data) => {
        const errors = {};
        if (!Validator.isEmail(data.email)) errors.email = "ایمیل نامعتبر است";
        if (!data.password) errors.password = "رمز را وارد نکرده ابد";
        if (!data.name) errors.name = "نام خود را وارد کنید";
        if (data.password.length<8) errors.password = "رمز حداقل باید 8 حرف باشد";
        return errors;
    }

    onChange = e =>
        this.setState({
            data: { ...this.state.data, [e.target.name]: e.target.value }
        });


    showErrorMessage = () => {
        Alert.error(this.props.errosMessage, {
            position: 'bottom-right',
            effect: 'scale',
            beep: false,
            timeout: 5000,
            offset: 100
        });
        this.props.emptyErrorMessage(null);
    }

    render() {
        if (this.props.token) {
            return <Redirect to="/" />;
        }
        if(this.props.errosMessage) {this.showErrorMessage();}
        const { data, errors } = this.state;
        const loading = this.props.loading;
        return (
            <div className="container signupWidth">
              <CardWrapper>
                <h1>ثبت نام</h1>
                <hr/>
                <GoogleSingup text="ثبت نام با گوگل"/>
                <br/>
                <div className="text-center">
                    <strong>یا</strong>
                </div>
                <br/>
                <form onSubmit={this.sendData}>
                 <div className="form-group text-right">
                    <label htmlFor="name">نام</label>
                    <input name="name" value={data.name} onChange={this.onChange} type="text" className="form-control" id="name" aria-describedby="name"
                           placeholder="نام خود را وارد کنید"/>
                    {errors.name && <InlineError text={errors.name} />}
                 </div>
                 <div className="form-group text-right">
                    <label htmlFor="exampleInputEmail1">ایمیل</label>
                    <input name="email" value={data.email} onChange={this.onChange} type="email" className="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                           placeholder="ایمیل خود را وارد کنید"/>
                    {errors.email && <InlineError text={errors.email} />}
                 </div>
                 <div className="form-group text-right">
                    <label htmlFor="exampleInputPassword1">رمز</label>
                    <input name="password" value={data.password} onChange={this.onChange} type="password" className="form-control" id="exampleInputPassword1" placeholder="رمز خود را وارد کنید"/>
                    {errors.password && <InlineError text={errors.password} />}
                 </div>
                 <div className="flex-row space-between">
                    <div>
                        <button hidden={loading} onClick={this.sendData} type="submit" className="btn btn-success">ثبت نام</button>
                        <input type="submit" style={{display: 'none'}} />
                        <ClipLoader color={'#123abc'} loading={loading} />
                    </div>
                    <Link to="/Login">ثبت نام کرده ام</Link>
                 </div>
                </form>
                <br/>
                <br/>
              </CardWrapper>
              <br/>
              <br/>
              <br/>
            </div>
        )
    }
}

const mapStateToProps = state => {
    return {
        token: state.auth.token,
        loading: state.auth.loading,
        errosMessage: state.auth.error
    }
}

const mapDispatchToProps = dispatch => {
    return {
        checkAuthState: () => dispatch( actions.authCheckState() ),
        registerUser: (email, password, name, url) => dispatch( actions.registerUser(email, password, name, url) ),
        emptyErrorMessage: (error) => dispatch( actions.authFail(error) )
    };
};


export default connect(mapStateToProps,mapDispatchToProps)(Signup);