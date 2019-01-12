import React , {Component} from 'react';
import URLs from '../../../URLs';
import InlineError from  '../../messages/InlineError';
import "react-tabs/style/react-tabs.css";
import { connect } from 'react-redux';

class SlidesManager extends Component {
    state = {
        data: {
            imageFile1: null, imageFile2: null, imageFile3: null,
            textSlide1: '', textSlide2: '', textSlide3: '',
            textButtonSlide1: '', textButtonSlide2: '', textButtonSlide3: '',
            buttonLink1: '', buttonLink2: '', buttonLink3: ''
        },
        errors: {}
    }

    validate = (data) => {
        const errors = {};
        if (!data.imageFile1) errors.imageFile1 = "Can't be blank";
        if (!data.imageFile2) errors.imageFile2 = "Can't be blank";
        if (!data.imageFile3) errors.imageFile3 = "Can't be blank";
        if (!data.textSlide1) errors.textSlide1 = "Can't be blank";
        if (!data.textSlide2) errors.textSlide2 = "Can't be blank";
        if (!data.textSlide3) errors.textSlide3 = "Can't be blank";
        if (!data.textButtonSlide1) errors.textButtonSlide1 = "Can't be blank";
        if (!data.textButtonSlide2) errors.textButtonSlide2 = "Can't be blank";
        if (!data.textButtonSlide3) errors.textButtonSlide3 = "Can't be blank";
        if (!data.buttonLink1) errors.buttonLink1 = "Can't be blank";
        if (!data.buttonLink2) errors.buttonLink2 = "Can't be blank";
        if (!data.buttonLink3) errors.buttonLink3 = "Can't be blank";
        return errors;
    }

    onChange = e =>
        this.setState({
            data: { ...this.state.data, [e.target.name]: e.target.value }
        });

    onChangeFile = e => {
        console.log("image :");
        this.setState({
            data: {...this.state.data, [e.target.name]: e.target.files[0]}
        });
    }

    render() {
        const { data, errors } = this.state;
        return (
            <div className="container" style={{textAlign: "right"}}>
                <form method="post" action={URLs.base_URL+URLs.cm_add_image}  encType="multipart/form-data">
                    <input hidden type="text" name="token" value={this.props.token} />
                    <div className="form-group">
                        <label>عکس اسلاید 1 </label>
                        <input name="imageFile1" onChange={this.onChangeFile} type="file" className="form-control"/>
                        {errors.imageFile1 && <InlineError text={errors.imageFile1} />}
                    </div>
                    <div className="form-group">
                        <label>متن اسلاید 1</label>
                        <input name="textSlide1" value={data.textSlide1} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textSlide1 && <InlineError text={errors.textSlide1} />}
                    </div>
                    <div className="form-group">
                        <label>متن لینک 1</label>
                        <input name="textButtonSlide1" value={data.textButtonSlide1} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textButtonSlide1 && <InlineError text={errors.textButtonSlide1} />}
                    </div>
                    <div className="form-group">
                        <label>لینک 1</label>
                        <input name="buttonLink1" value={data.buttonLink1} onChange={this.onChange} type="text" className="form-control"
                              />
                        {errors.buttonLink1 && <InlineError text={errors.buttonLink1} />}
                    </div>
                    <hr/>
                    <div className="form-group">
                        <label>عکس اسلاید 2 </label>
                        <input name="imageFile2" onChange={this.onChangeFile} type="file" className="form-control"/>
                        {errors.imageFile2 && <InlineError text={errors.imageFile2} />}
                    </div>
                    <div className="form-group">
                        <label>متن اسلاید 2</label>
                        <input name="textSlide2" value={data.textSlide2} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textSlide2 && <InlineError text={errors.textSlide2} />}
                    </div>
                    <div className="form-group">
                        <label>متن لینک 2</label>
                        <input name="textButtonSlide2" value={data.textButtonSlide2} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textButtonSlide2 && <InlineError text={errors.textButtonSlide2} />}
                    </div>
                    <div className="form-group">
                        <label>لینک 2</label>
                        <input name="buttonLink2" value={data.buttonLink2} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.buttonLink2 && <InlineError text={errors.buttonLink2} />}
                    </div>
                    <hr/>
                    <div className="form-group">
                        <label>عکس اسلاید 3 </label>
                        <input name="imageFile3" onChange={this.onChangeFile} type="file" className="form-control"/>
                        {errors.imageFile3 && <InlineError text={errors.imageFile3} />}
                    </div>
                    <div className="form-group">
                        <label>متن اسلاید 3</label>
                        <input name="textSlide3" value={data.textSlide3} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textSlide3 && <InlineError text={errors.textSlide3} />}
                    </div>
                    <div className="form-group">
                        <label>متن لینک 3</label>
                        <input name="textButtonSlide3" value={data.textButtonSlide3} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.textButtonSlide3 && <InlineError text={errors.textButtonSlide3} />}
                    </div>
                    <div className="form-group">
                        <label>لینک 3</label>
                        <input name="buttonLink3" value={data.buttonLink3} onChange={this.onChange} type="text" className="form-control"
                               />
                        {errors.buttonLink3 && <InlineError text={errors.buttonLink3} />}
                    </div>
                    <br/>
                    <button className="btn btn-primary" style={{display: "block", margin: "auto"}}>ذخیره اطلاعات</button>
                    <br/><br/>
                </form>
            </div>
        )
    }
};
const mapStateToProps = state => {
    return {
        token: state.auth.token
    }
}

export default connect(mapStateToProps,null)(SlidesManager);
