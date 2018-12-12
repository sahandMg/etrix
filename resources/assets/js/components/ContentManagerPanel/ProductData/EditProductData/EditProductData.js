import React , {Component} from 'react';
import axios from 'axios';
import dataCode from '../../../../dataCode';
import URLs from "../../../../URLs";
import Validator from 'validator';
import InlineError from '../../../messages/InlineError';
import { ClipLoader } from 'react-spinners';

class EditProductData extends Component {

    state = {
        searchKey: '', data: '', dataParts: [], dataCode: '', dataFilters: [],
        tableHeaderS: '', loading: false, errors: {}
    }

    componentDidMount() {

    }

    searchroductData = (event) => {
        event.preventDefault();
        const errors = this.validate(this.state.searchKey);
        this.setState({ errors });
        if (Object.keys(errors).length === 0) {
            let url = URLs.base_URL + URLs.search_part_category + 'all&keyword=' + this.state.searchKey;
            this.setState({loading: true});
            axios.get(url)
                .then(response => {
                    console.log("searchroductData");
                    console.log(response);
                    // console.log(dataCode.partSearch);
                    if (response.data[0] === dataCode.partSearch) {
                        // console.log("IS EQUAL");
                        this.setState({
                            dataCode: response.data[0],
                            dataParts: response.data[2],
                            dataFilters: response.data[3],
                            tableHeaderS: response.data[5]
                        });
                    }
                    this.setState({loading: false});
                })
                .catch(err => {
                    console.log("searchroductData searchKey");
                    console.log(err);
                });
        }
    }

    validate = (data) => {
        const errors = {};
        if (!data) errors.searchKey = "نام قطعه را وارد نکرده ابد";
        return errors;
    }

    onChange = e =>
        this.setState({
            [e.target.name]: e.target.value
        });

    render() {
        const { errors } = this.state;
        let information;
        if(this.state.dataParts.length === 1) {
            // information =
        }
        return (
            <div className="container">
                <form onSubmit={this.searchroductData} className="text-right" style={{direction: "rtl"}}>
                    <div className="form-group">
                        <label>اسم قطعه</label>
                        <input name="searchKey" value={this.state.searchKey} onChange={this.onChange} type="text" className="form-control"
                               placeholder="نام قطعه را وارد کنید"/>
                        {errors.searchKey && <InlineError text={errors.searchKey} />}
                    </div>
                    <div>
                      <button  hidden={this.state.loading} type="submit" className="btn btn-success">جست و جو</button>
                      <ClipLoader color={'#123abc'} loading={this.state.loading} />
                    </div>
                </form>
                <br/><br/>
                <form>
                    {information}
                </form>
            </div>
        )
    }
};

export default EditProductData;